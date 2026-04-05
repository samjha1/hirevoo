<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateProfile extends Model
{
    /** @return list<string> */
    public static function educationDegreeValues(): array
    {
        return [
            'B.Tech', 'B.E.', 'BCA', 'B.Sc', 'B.Com', 'BBA', 'BA',
            'MBA', 'M.Tech', 'M.E.', 'M.Sc', 'M.Com', 'CA', 'Diploma', 'LLB', 'MBBS', 'Ph.D', 'Other',
        ];
    }

    public function formattedTotalExperience(): ?string
    {
        if ($this->experience_years === null && $this->experience_months === null) {
            return null;
        }
        $y = (int) ($this->experience_years ?? 0);
        $m = (int) ($this->experience_months ?? 0);
        $parts = [];
        if ($y > 0) {
            $parts[] = $y.' '.($y === 1 ? 'year' : 'years');
        }
        if ($m > 0) {
            $parts[] = $m.' '.($m === 1 ? 'month' : 'months');
        }
        if ($parts === []) {
            return '0 months';
        }

        return implode(' ', $parts);
    }

    public function formattedExpectedSalary(): ?string
    {
        if ($this->expected_salary === null || trim((string) $this->expected_salary) === '') {
            return null;
        }
        $currency = $this->expected_salary_currency ?? 'INR';
        $period = ($this->expected_salary_period ?? 'per_annum') === 'per_month'
            ? 'per month'
            : 'per annum';

        return trim($currency.' '.$this->expected_salary.' ('.$period.')');
    }

    /**
     * Normalize JSON repeater columns for views (casts are usually arrays; raw DB/API may return a JSON string).
     *
     * @return list<array<string, mixed>>
     */
    public static function jsonRepeaterToArray(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        if (is_array($value)) {
            return array_is_list($value) ? $value : array_values($value);
        }
        if (is_string($value)) {
            $t = trim($value);
            if ($t === '' || strcasecmp($t, 'null') === 0) {
                return [];
            }
            $decoded = json_decode($t, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            if (is_array($decoded)) {
                return array_is_list($decoded) ? $decoded : array_values($decoded);
            }
        }

        return [];
    }

    protected $fillable = [
        'user_id',
        'profile_photo_path',
        'headline',
        'bio_summary',
        'career_objective',
        'current_company',
        'education',
        'education_history',
        'experience_years',
        'experience_months',
        'skills',
        'tools',
        'technical_skill_level',
        'location',
        'date_of_birth',
        'gender',
        'preferred_job_location',
        'preferred_job_role',
        'job_type',
        'notice_period',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'work_experience',
        'projects',
        'certifications',
        'current_salary',
        'expected_salary',
        'expected_salary_currency',
        'expected_salary_period',
        'is_premium',
        'premium_expires_at',
        'referral_requests_used',
        'referral_requests_limit',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'premium_expires_at' => 'datetime',
            'date_of_birth' => 'date',
            'work_experience' => 'array',
            'education_history' => 'array',
            'projects' => 'array',
            'certifications' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Absolute URL for a path on the public disk (e.g. profile-photos/…).
     * Uses the incoming request’s scheme/host/base path when available so subdirectory installs work.
     * Unlike asset(), this is not overridden by ASSET_URL, which often breaks storage URLs.
     */
    public static function publicDiskFileUrl(string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        $req = request();
        if ($req->getHttpHost() !== '') {
            $root = rtrim($req->getSchemeAndHttpHost().$req->getBasePath(), '/');

            return $root.'/storage/'.$relativePath;
        }

        return rtrim((string) config('app.url'), '/').'/storage/'.$relativePath;
    }

    public function profilePhotoUrl(): ?string
    {
        if (! filled($this->profile_photo_path)) {
            return null;
        }

        return self::publicDiskFileUrl($this->profile_photo_path);
    }

    /**
     * Rough profile strength for the candidate UI (0–100).
     *
     * @return array{percent: int, filled: int, total: int}
     */
    public static function completionStats(?self $profile, User $user): array
    {
        if (! $user->isCandidate()) {
            return ['percent' => 0, 'filled' => 0, 'total' => 0];
        }

        $p = $profile;

        /** JSON columns may be arrays (casts) or raw JSON strings from the DB. */
        $jsonToArray = static function (mixed $value): ?array {
            if ($value === null) {
                return null;
            }
            if (is_array($value)) {
                return $value;
            }
            if (is_string($value)) {
                $t = trim($value);
                if ($t === '' || strcasecmp($t, 'null') === 0) {
                    return null;
                }
                $decoded = json_decode($t, true);
                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                    return null;
                }

                return $decoded;
            }

            return null;
        };

        $hasJsonRows = static function (mixed $json) use ($jsonToArray): bool {
            $arr = $jsonToArray($json);
            if ($arr === null || $arr === []) {
                return false;
            }
            foreach ($arr as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $s = trim(implode(' ', array_map(fn ($v) => is_scalar($v) ? (string) $v : '', $row)));

                if ($s !== '') {
                    return true;
                }
            }

            return false;
        };

        $checks = [
            trim((string) $user->name) !== '',
            trim((string) $user->phone) !== '',
            $p && filled($p->headline),
            $p && filled($p->location),
            $p && filled($p->education),
            $p && filled($p->skills),
            $p && $p->experience_years !== null,
            $p && (filled($p->bio_summary) || filled($p->career_objective)),
            $p && filled($p->profile_photo_path),
            $p && $hasJsonRows($p->work_experience),
            $p && $hasJsonRows($p->education_history),
            $p && ($hasJsonRows($p->projects) || $hasJsonRows($p->certifications)),
            $p && (filled($p->linkedin_url) || filled($p->github_url) || filled($p->portfolio_url)),
            $p && (filled($p->preferred_job_role) || filled($p->preferred_job_location) || filled($p->job_type) || filled($p->expected_salary)),
            $p && filled($p->tools),
            $user->resumes()->exists(),
        ];

        $total = count($checks);
        $filled = count(array_filter($checks));
        $percent = $total > 0 ? (int) round(100 * $filled / $total) : 0;

        return [
            'percent' => min(100, $percent),
            'filled' => $filled,
            'total' => $total,
        ];
    }

    /**
     * Accordion section completion for profile UI checkmarks (keys 1–9).
     *
     * @return array<int, bool>
     */
    public static function sectionCompletionFlags(?self $profile, User $user): array
    {
        if (! $user->isCandidate()) {
            return [];
        }

        $p = $profile;
        $hasRepeater = static function (string $attribute) use ($p): bool {
            if ($p === null) {
                return false;
            }
            $arr = self::jsonRepeaterToArray($p->{$attribute} ?? null);
            foreach ($arr as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $s = trim(implode(' ', array_map(fn ($v) => is_scalar($v) ? (string) $v : '', $row)));
                if ($s !== '') {
                    return true;
                }
            }

            return false;
        };

        return [
            1 => trim((string) $user->name) !== ''
                && trim((string) ($user->phone ?? '')) !== ''
                && $p !== null
                && trim((string) ($p->headline ?? '')) !== ''
                && trim((string) ($p->location ?? '')) !== '',
            2 => $p !== null && (filled($p->bio_summary) || filled($p->career_objective)),
            3 => $hasRepeater('work_experience'),
            4 => $p !== null && (filled($p->education) || $hasRepeater('education_history')),
            5 => $p !== null && filled($p->skills) && $p->experience_years !== null,
            6 => $hasRepeater('projects'),
            7 => $hasRepeater('certifications'),
            8 => $p !== null && (filled($p->linkedin_url) || filled($p->github_url) || filled($p->portfolio_url)),
            9 => $p !== null && (
                filled($p->preferred_job_role)
                || filled($p->preferred_job_location)
                || filled($p->job_type)
                || filled($p->expected_salary)
                || filled($p->notice_period)
                || filled($p->current_salary)
            ),
        ];
    }
}
