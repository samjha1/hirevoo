<?php

namespace App\Services;

use App\Models\CandidateProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CandidateProfileFillerFromResume
{
    public function __construct(
        protected GptService $gptService,
        protected ResumeAnalysisService $resumeAnalysis
    ) {}

    /**
     * Parse latest resume and merge into candidate profile. Returns true if GPT path succeeded.
     */
    public function fill(User $user): bool
    {
        if (! $user->isCandidate()) {
            return false;
        }

        $resume = $user->resumes()->orderByDesc('created_at')->first();
        if (! $resume) {
            return false;
        }

        $path = storage_path('app/'.$resume->file_path);
        if (! is_readable($path)) {
            return false;
        }

        $text = $this->resumeAnalysis->extractTextFromFile($path, $resume->mime_type ?? 'application/pdf');

        if ($this->gptService->isAvailable() && trim($text) !== '') {
            $data = $this->gptService->extractProfileFromResume($text);
            if ($data) {
                $this->applyExtractedData($user, $data);
                $user->refresh();
                $user->syncCandidateProfileCompletion();

                return true;
            }
        }

        $this->resumeAnalysis->fillProfileFromResumeFallback($resume);
        $this->resumeAnalysis->fillProfileBioFromResumeSummary($resume);
        $user->refresh();
        $user->syncCandidateProfileCompletion();

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyExtractedData(User $user, array $data): void
    {
        $userUpdates = [];
        if (! empty($data['full_name']) && trim((string) ($user->name ?? '')) === '') {
            $userUpdates['name'] = Str::limit(trim((string) $data['full_name']), 255, '');
        }
        if (! empty($data['phone'])) {
            $userUpdates['phone'] = Str::limit(trim((string) $data['phone']), 30, '');
        }
        if ($userUpdates !== []) {
            $user->update($userUpdates);
        }

        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);

        $stringFields = [
            'headline', 'bio_summary', 'career_objective', 'education', 'skills', 'tools', 'location',
            'current_company', 'linkedin_url', 'github_url', 'portfolio_url',
            'preferred_job_role', 'preferred_job_location', 'job_type', 'notice_period',
            'expected_salary', 'gender',
        ];
        foreach ($stringFields as $k) {
            if (! empty($data[$k]) && is_string($data[$k])) {
                $v = trim($data[$k]);
                if ($v !== '') {
                    $profile->{$k} = match ($k) {
                        'bio_summary', 'career_objective' => Str::limit($v, 5000, ''),
                        'skills', 'tools' => Str::limit($v, 4000, ''),
                        default => Str::limit($v, 255, ''),
                    };
                }
            }
        }

        if (isset($data['experience_years']) && is_numeric($data['experience_years'])) {
            $profile->experience_years = (int) $data['experience_years'];
        }

        $level = isset($data['technical_skill_level']) ? trim((string) $data['technical_skill_level']) : '';
        if (in_array($level, ['Beginner', 'Intermediate', 'Expert'], true)) {
            $profile->technical_skill_level = $level;
        }

        if (! empty($data['date_of_birth'])) {
            try {
                $profile->date_of_birth = Carbon::parse($data['date_of_birth'])->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        foreach (['work_experience', 'education_history', 'projects', 'certifications'] as $jsonKey) {
            if (! empty($data[$jsonKey]) && is_array($data[$jsonKey])) {
                $filtered = $this->filterNonEmptyRows($data[$jsonKey]);
                $profile->{$jsonKey} = $filtered === [] ? null : $filtered;
            }
        }

        $profile->save();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    protected function filterNonEmptyRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $flat = implode('', array_map(fn ($v) => is_scalar($v) ? (string) $v : '', $row));
            if (trim($flat) !== '') {
                $out[] = $row;
            }
        }

        return $out;
    }
}
