<?php

namespace App\Services;

use App\Models\LeadsmanagerAd;
use Illuminate\Support\Facades\Schema;

class LeadsmanagerAdService
{
    public function forPlacement(string $placement): ?array
    {
        if (! config('leadsmanager.enabled', true)) {
            return null;
        }

        if (! Schema::hasTable('leadsmanager_ads')) {
            return null;
        }

        $ad = LeadsmanagerAd::query()
            ->approvedForPlacement($placement)
            ->inRandomOrder()
            ->first();

        if (! $ad) {
            return null;
        }

        $base = rtrim((string) config('leadsmanager.api_base_url', ''), '/');
        if ($base === '') {
            $base = rtrim((string) env('LEADSMANAGER_URL', 'http://localhost/themesdesign.in/leadsmanager/public'), '/');
        }

        $tags = [];
        if (! empty($ad->target_audience)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', (string) $ad->target_audience))));
            $tags = array_slice($tags, 0, 5);
        }

        return [
            'public_key' => $ad->public_key,
            'headline' => $ad->headline,
            'body' => $ad->body,
            'image_url' => $ad->publicImageUrl(),
            'cta_label' => $ad->cta_label ?: 'Explore now',
            'tags' => $tags,
            'click_url' => "{$base}/api/track/click/{$ad->public_key}",
            'impression_url' => "{$base}/api/track/impression/{$ad->public_key}",
            'placement' => $placement,
        ];
    }
}
