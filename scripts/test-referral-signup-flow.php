<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReferrerProfile;
use App\Models\User;
use App\Services\CrmEmployerProspectBridge;
use Illuminate\Support\Facades\DB;

$code = $argv[1] ?? null;
if (! $code) {
    fwrite(STDERR, "Usage: php scripts/test-referral-signup-flow.php EMP-XXXXXX\n");
    exit(1);
}

$email = 'referral-flow-'.time().'@hirevoo.test';

$user = User::query()->create([
    'name' => 'Referral Flow Test Co',
    'email' => $email,
    'phone' => '9000000001',
    'password' => bcrypt('password'),
    'role' => 'referrer',
    'status' => 'active',
]);

ReferrerProfile::query()->create([
    'user_id' => $user->id,
    'company_name' => 'Referral Flow Test Co',
    'company_email' => $email,
    'referral_code' => $code,
    'is_approved' => false,
    'credits' => 5,
]);

app(CrmEmployerProspectBridge::class)->syncReferrerSignup($user->fresh(['referrerProfile']));

$prospect = DB::table('crm_employer_prospects')->where('user_id', $user->id)->first();
$admin = DB::table('admins')->whereRaw('UPPER(referral_code) = ?', [strtoupper($code)])->first();

echo "user_id={$user->id}\n";
echo 'referral_code='.$code."\n";
echo 'prospect_id='.($prospect->id ?? 'none')."\n";
echo 'assigned_to='.($prospect->assigned_to ?? 'none')."\n";
echo 'expected_admin='.($admin->id ?? 'none')." ({$admin->email})\n";
echo 'source='.($prospect->source ?? 'none')."\n";

if ($prospect && $admin && (int) $prospect->assigned_to === (int) $admin->id) {
    echo "OK: auto-assigned to referral code owner\n";
    exit(0);
}

echo "FAIL: not auto-assigned\n";
exit(1);
