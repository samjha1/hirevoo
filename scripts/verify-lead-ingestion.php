<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lead;
use App\Models\User;
use App\Services\CandidateLeadService;

$email = 'live.pipeline.test@hirevoo.test';
$user = User::query()->firstOrCreate(
    ['email' => $email],
    ['name' => 'Live Pipeline Test', 'role' => 'candidate', 'password' => bcrypt('password'), 'status' => 'active'],
);

$svc = app(CandidateLeadService::class);
$before = Lead::query()->where('candidate_id', $user->id)->count();
$lead = $svc->ensureCandidateCrmLead($user->id, 'verify_script');
$after = Lead::query()->where('candidate_id', $user->id)->count();

echo "user_id={$user->id}\n";
echo "leads_before={$before} leads_after={$after}\n";
echo 'lead_id='.($lead->id ?? 'none')."\n";
echo $after === 1 ? "OK: single lead row per candidate\n" : "WARN: expected exactly 1 lead row, got {$after}\n";
