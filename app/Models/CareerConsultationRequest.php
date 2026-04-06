<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerConsultationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'job_role_id',
        'resume_id',
        'source',
        'match_percentage',
        'gap_skills',
        'suggested_gap_skills',
        'matched_skills',
        'status',
    ];

    /**
     * Laravel 10 reads the $casts property (not a casts() method) for JSON/array columns.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gap_skills' => 'array',
        'suggested_gap_skills' => 'array',
        'matched_skills' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    /**
     * Ensure at least $min “to strengthen” topics for UX + consult: real gaps first, then role-based suggestions.
     *
     * @param  list<string>  $actualGapLabels
     * @param  list<string>  $matchedLabels
     * @return array{display_gaps: list<string>, suggested_only: list<string>, actual_gaps: list<string>}
     */
    public static function buildConsultGapPayload(?JobRole $role, array $actualGapLabels, array $matchedLabels, int $min = 3): array
    {
        $actualGapLabels = array_values(array_filter(array_map(fn ($s) => is_string($s) ? trim($s) : '', $actualGapLabels), fn ($s) => $s !== ''));
        $matchedLabels = array_values(array_filter(array_map(fn ($s) => is_string($s) ? trim($s) : '', $matchedLabels), fn ($s) => $s !== ''));

        $display = $actualGapLabels;
        $suggested = [];

        if (count($display) >= $min) {
            return [
                'display_gaps' => $display,
                'suggested_only' => [],
                'actual_gaps' => $actualGapLabels,
            ];
        }

        $have = array_map(fn ($s) => mb_strtolower($s), array_merge($matchedLabels, $actualGapLabels));
        $pool = self::suggestionPoolForRole($role);

        foreach ($pool as $s) {
            if (count($display) >= $min) {
                break;
            }
            $sl = mb_strtolower(trim($s));
            if ($sl === '' || in_array($sl, $have, true)) {
                continue;
            }
            $display[] = $s;
            $suggested[] = $s;
            $have[] = $sl;
        }

        return [
            'display_gaps' => $display,
            'suggested_only' => $suggested,
            'actual_gaps' => $actualGapLabels,
        ];
    }

    /**
     * @return list<string>
     */
    protected static function suggestionPoolForRole(?JobRole $role): array
    {
        $base = [
            'System design thinking',
            'Cross-team communication',
            'Technical writing & docs',
            'Mentoring & code review',
            'Performance optimization',
            'Security & privacy awareness',
            'CI/CD & release quality',
            'Testing strategy',
            'Stakeholder management',
        ];

        if (! $role) {
            return $base;
        }

        $t = mb_strtolower($role->title);

        $extra = [];
        if (str_contains($t, 'flutter') || str_contains($t, 'mobile') || str_contains($t, 'ios') || str_contains($t, 'android')) {
            $extra = [
                'App store & release process',
                'Native platform bridges',
                'App performance profiling',
                'Accessibility (mobile)',
            ];
        } elseif (str_contains($t, 'frontend') || str_contains($t, 'react') || str_contains($t, 'vue') || str_contains($t, 'angular')) {
            $extra = [
                'Accessibility (WCAG)',
                'Design systems',
                'Frontend performance budgets',
            ];
        } elseif (str_contains($t, 'backend') || str_contains($t, 'api') || str_contains($t, 'engineer')) {
            $extra = [
                'API versioning & contracts',
                'Observability & logging',
                'Data modeling',
            ];
        } elseif (str_contains($t, 'data')) {
            $extra = [
                'Data quality & governance',
                'Stakeholder storytelling',
            ];
        }

        return array_values(array_unique(array_merge($extra, $base)));
    }
}
