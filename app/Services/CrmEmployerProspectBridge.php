<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Writes employer signups into the shared CRM tables (adminpanal company pipeline).
 */
class CrmEmployerProspectBridge
{
    public function syncReferrerSignup(User $user): bool
    {
        if (! $user->isReferrer() || ! Schema::hasTable('crm_employer_prospects')) {
            return false;
        }

        try {
            return DB::transaction(function () use ($user): bool {
                $user->loadMissing('referrerProfile');
                $profile = $user->referrerProfile;
                if (! $profile) {
                    return false;
                }

                $now = now();
                $existing = DB::table('crm_employer_prospects')
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                $profilePayload = [
                    'company_name' => $profile->company_name ?? $user->name ?? ('Company #'.$user->id),
                    'contact_name' => $user->name,
                    'email' => $profile->company_email ?? $user->email,
                    'phone' => $user->phone,
                    'updated_at' => $now,
                ];

                if ($existing) {
                    DB::table('crm_employer_prospects')
                        ->where('id', $existing->id)
                        ->update($profilePayload);

                    $prospectId = (int) $existing->id;
                    $assignedTo = $existing->assigned_to;
                    $pipelineStage = $existing->pipeline_stage;
                } else {
                    $insert = array_merge($profilePayload, [
                        'user_id' => $user->id,
                        'source' => 'hirevo_signup',
                        'pipeline_stage' => 'lead_generated',
                        'win_probability' => 10,
                        'assignment_status' => 'new',
                        'sales_status' => 'pending',
                        'crm_stage' => 'new',
                        'created_at' => $now,
                    ]);

                    $prospectId = (int) DB::table('crm_employer_prospects')->insertGetId($insert);
                    $assignedTo = null;
                    $pipelineStage = 'lead_generated';
                }

                if (! $pipelineStage) {
                    DB::table('crm_employer_prospects')->where('id', $prospectId)->update([
                        'pipeline_stage' => 'lead_generated',
                        'win_probability' => 10,
                        'updated_at' => $now,
                    ]);
                }

                if (! $assignedTo && filled($profile->referral_code)) {
                    $this->assignFromReferralCode($prospectId, (string) $profile->referral_code);
                }

                return true;
            });
        } catch (Throwable $e) {
            Log::error('CRM employer prospect sync failed after referrer signup', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function assignFromReferralCode(int $prospectId, string $code): void
    {
        if (! Schema::hasTable('admins') || ! Schema::hasColumn('admins', 'referral_code')) {
            return;
        }

        $normalized = strtoupper(trim($code));
        if ($normalized === '') {
            return;
        }

        $admin = DB::table('admins')
            ->whereRaw('UPPER(referral_code) = ?', [$normalized])
            ->where('sales_team', 'employer')
            ->whereIn('role', ['sales_manager', 'sales_employee'])
            ->first();

        if (! $admin) {
            return;
        }

        $update = [
            'assigned_to' => $admin->id,
            'updated_at' => now(),
            'source' => 'hirevo_referral',
        ];

        if ($admin->role === 'sales_manager') {
            $update['sales_manager_id'] = $admin->id;
            $update['assignment_role_level'] = 'manager';
            $update['assignment_status'] = 'assigned';
        } else {
            $update['sales_manager_id'] = $admin->manager_id;
            $update['assignment_role_level'] = 'employee';
            $update['assignment_status'] = 'in_progress';
        }

        DB::table('crm_employer_prospects')
            ->where('id', $prospectId)
            ->whereNull('assigned_to')
            ->update($update);
    }
}
