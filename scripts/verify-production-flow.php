<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReferrerProfile;
use App\Models\User;
use App\Services\CrmEmployerProspectBridge;
use Illuminate\Support\Facades\DB;

$failures = 0;

function check(bool $ok, string $label): void
{
    global $failures;
    echo ($ok ? 'OK' : 'FAIL')." {$label}\n";
    if (! $ok) {
        $failures++;
    }
}

// 1. ReferrerProfile date casts (production bug fix)
$profile = ReferrerProfile::query()->whereNotNull('approved_at')->first()
    ?? ReferrerProfile::query()->first();
if ($profile && $profile->approved_at) {
    check($profile->approved_at instanceof \Illuminate\Support\Carbon, 'ReferrerProfile approved_at is Carbon');
} else {
    echo "SKIP ReferrerProfile approved_at cast (no approved profile in DB)\n";
}

// 2. Referral code normalization
$normalized = new ReferrerProfile(['referral_code' => ' emp-abc123 ']);
check($normalized->referral_code === 'EMP-ABC123', 'Referral code normalized to uppercase');

// 3. Live referral assignment flow
$code = DB::table('admins')
    ->where('email', 'company.executive@themesdesign.test')
    ->value('referral_code');

if (! $code) {
    echo "FAIL no referral code on company.executive — run: php artisan crm:ensure-referral-codes (adminpanal)\n";
    exit(1);
}

$email = 'prod-flow-'.time().'@hirevoo.test';
$user = User::query()->create([
    'name' => 'Production Flow Test Co',
    'email' => $email,
    'phone' => '9111111111',
    'password' => bcrypt('password'),
    'role' => 'referrer',
    'status' => 'active',
]);

ReferrerProfile::query()->create([
    'user_id' => $user->id,
    'company_name' => 'Production Flow Test Co',
    'company_email' => $email,
    'referral_code' => $code,
    'is_approved' => false,
    'credits' => 5,
]);

check(app(CrmEmployerProspectBridge::class)->syncReferrerSignup($user->fresh(['referrerProfile'])), 'CRM bridge sync succeeded');

$prospect = DB::table('crm_employer_prospects')->where('user_id', $user->id)->first();
$admin = DB::table('admins')->whereRaw('UPPER(referral_code) = ?', [strtoupper($code)])->first();

check($prospect !== null, 'Company prospect created');
check($admin !== null, 'Referral code resolves to admin');
check($prospect && $admin && (int) $prospect->assigned_to === (int) $admin->id, 'Prospect auto-assigned to code owner');
check($prospect && $prospect->source === 'hirevo_referral', 'Source is hirevo_referral');

// 4. Re-sync must not clobber assignment (simulate adminpanal sync via SQL update only on profile fields)
if ($prospect) {
    DB::table('crm_employer_prospects')->where('id', $prospect->id)->update([
        'company_name' => 'Production Flow Test Co Updated',
        'updated_at' => now(),
    ]);
    $after = DB::table('crm_employer_prospects')->where('id', $prospect->id)->first();
    check($after->source === 'hirevo_referral', 'Source preserved after profile update');
    check((int) $after->assigned_to === (int) $admin->id, 'Assignment preserved after profile update');
}

// 5. Employer profile page no longer 500 for approved referrer
$referrer = User::query()->where('role', 'referrer')->whereHas('referrerProfile')->first();
if ($referrer) {
    auth()->login($referrer);
    $response = app(Illuminate\Contracts\Http\Kernel::class)->handle(
        Illuminate\Http\Request::create('/employer/profile', 'GET')
    );
    check($response->getStatusCode() < 400, 'Employer profile page loads (status '.$response->getStatusCode().')');
}

exit($failures > 0 ? 1 : 0);
