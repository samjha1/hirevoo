<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmployerJob extends Model
{
    protected $table = 'employer_jobs';

    protected $fillable = [
        'user_id',
        'company_name',
        'job_department',
        'required_skills',
        'title',
        'slug',
        'job_type',
        'is_night_shift',
        'description',
        'location',
        'work_location_type',
        'pay_type',
        'salary_min',
        'salary_max',
        'salary_amount',
        'experience_years',
        'perks',
        'joining_fee_required',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_night_shift' => 'boolean',
            'joining_fee_required' => 'boolean',
            'required_skills' => 'array',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'experience_years' => 'integer',
        ];
    }

    public static function humanizeLocationBoundaries(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return $text;
        }
        for ($i = 0; $i < 24; $i++) {
            $next = preg_replace('/(\p{Ll})(\p{Lu})/u', '$1 $2', $text);
            if ($next === null || $next === $text) {
                break;
            }
            $text = $next;
        }
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    protected function formattedLocation(): Attribute
    {
        return Attribute::get(function (): ?string {
            $raw = $this->attributes['location'] ?? null;
            if ($raw === null || $raw === '') {
                return null;
            }
            $raw = (string) $raw;
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $parts = array_filter([
                    $decoded['area'] ?? null,
                    $decoded['city'] ?? null,
                    $decoded['state'] ?? null,
                    $decoded['country'] ?? null,
                    $decoded['pincode'] ?? null,
                ], static fn ($v) => $v !== null && $v !== '');
                if ($parts !== []) {
                    $parts = array_map(fn ($p) => self::humanizeLocationBoundaries((string) $p), $parts);

                    return self::humanizeLocationBoundaries(implode(', ', $parts));
                }
            }

            return self::humanizeLocationBoundaries($raw);
        });
    }

    /** @return list<string> */
    protected function locationDisplayLines(): Attribute
    {
        return Attribute::get(function (): array {
            $raw = $this->attributes['location'] ?? null;
            if ($raw === null || $raw === '') {
                return [];
            }
            $rawStr = (string) $raw;
            $decoded = json_decode($rawStr, true);
            if (! is_array($decoded)) {
                return [self::humanizeLocationBoundaries($rawStr)];
            }
            $area = trim((string) ($decoded['area'] ?? ''));
            $area = $area === '' ? '' : self::humanizeLocationBoundaries($area);
            $rest = array_filter([
                trim((string) ($decoded['city'] ?? '')),
                trim((string) ($decoded['state'] ?? '')),
                trim((string) ($decoded['country'] ?? '')),
                trim((string) ($decoded['pincode'] ?? '')),
            ], static fn ($v) => $v !== '');
            $line2 = implode(', ', $rest);
            $lines = [];
            if ($area !== '') {
                $lines[] = $area;
            }
            if ($line2 !== '') {
                $lines[] = $line2;
            }

            return $lines !== [] ? $lines : [self::humanizeLocationBoundaries($rawStr)];
        });
    }

    protected function formattedSalarySummary(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (($this->pay_type ?? '') === 'not_disclosed') {
                return null;
            }
            if ($this->salary_min !== null || $this->salary_max !== null) {
                $a = $this->salary_min !== null ? '₹' . number_format((int) $this->salary_min) : '—';
                $b = $this->salary_max !== null ? '₹' . number_format((int) $this->salary_max) : '—';

                return "{$a} – {$b}";
            }
            $amt = $this->salary_amount ?? null;
            if ($amt === null || $amt === '') {
                return null;
            }
            if (is_numeric($amt)) {
                return '₹' . number_format((int) $amt);
            }

            return (string) $amt;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(EmployerJobApplication::class, 'employer_job_id');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug) && ! empty($model->title)) {
                $model->slug = Str::slug($model->title) . '-' . substr(uniqid(), -5);
            }
        });
    }
}
