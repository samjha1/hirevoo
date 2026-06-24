<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$credits = max(0, (int) config('hirevo_plans.employer_approval_credits', 1));

$updated = App\Models\ReferrerProfile::query()
    ->where('company_email_verified', true)
    ->where('is_approved', true)
    ->where('credits', 0)
    ->update(['credits' => $credits]);

echo "Granted {$credits} credit(s) to {$updated} verified employer profile(s).\n";
