<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Writes employer signups into the shared CRM tables (adminpanal company pipeline).
 */
class CrmEmployerProspectBridge
{
    public function syncReferrerSignup(User $user): void
    {
        if (! $user->isReferrer() || ! Schema::hasTable('crm_employer_prospects')) {
            return;
        }

        $user->loadMissing('referrerProfile');
        $profile = $user->referrerProfile;
        if (! $profile) {
            return;
        }

        $now = now();
        $existing = DB::table('crm_employer_prospects')->where('user_id', $user->id)->first();

        $payload = [
            'company_name' => $profile->company_name ?? $user->name ?? ('Company #'.$user->id),
            'contact_name' => $user->name,
            'email' => $profile->company_email ?? $user->email,
            'phone' => $user->phone,
            'source' => 'hirevo_signup',
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('crm_employer_prospects')->where('id', $existing->id)->update($payload);
            $prospectId = (int) $existing->id;
            $assignedTo = $existing->assigned_to;
            $pipelineStage = $existing->pipeline_stage;
        } else {
            $payload['user_id'] = $user->id;
            $payload['pipeline_stage'] = 'lead_generated';
            $payload['win_probability'] = 10;
            $payload['assignment_status'] = 'new';
            $payload['sales_status'] = 'pending';
            $payload['crm_stage'] = 'new';
            $payload['created_at'] = $now;
            $prospectId = (int) DB::table('crm_employer_prospects')->insertGetId($payload);
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

        if (! $assignedTo && $profile->referral_code) {
            $this->assignFromReferralCode($prospectId, $profile->referral_code);
        }
    }

    private function assignFromReferralCode(int $prospectId, string $code): void
    {
        if (! Schema::hasTable('admins') || ! Schema::hasColumn('admins', 'referral_code')) {
            return;
        }

        $admin = DB::table('admins')
            ->whereRaw('UPPER(referral_code) = ?', [strtoupper(trim($code))])
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

        DB::table('crm_employer_prospects')->where('id', $prospectId)->update($update);
    }
}
