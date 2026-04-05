<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('candidate_profile_completed_at')->nullable()->after('remember_token');
        });

        User::query()->where('role', 'candidate')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $user->load('candidateProfile');
                $p = $user->candidateProfile;
                if (! $p) {
                    continue;
                }
                $complete = $user->phone
                    && $p->headline
                    && $p->skills
                    && $p->location
                    && $p->education
                    && $p->experience_years !== null;
                if ($complete) {
                    $user->forceFill([
                        'candidate_profile_completed_at' => $p->updated_at ?? $user->updated_at,
                    ])->saveQuietly();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('candidate_profile_completed_at');
        });
    }
};
