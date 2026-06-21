<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only view of Ads Manager creatives (shared hirevo database).
 */
class LeadsmanagerAd extends Model
{
    protected $table = 'leadsmanager_ads';

    public $timestamps = true;

    protected $guarded = [];

    public function campaign()
    {
        return $this->belongsTo(LeadsmanagerCampaign::class, 'campaign_id');
    }

    public function scopeApprovedForPlacement($query, string $placement)
    {
        return $query
            ->where('placement', $placement)
            ->where('status', 'active')
            ->whereHas('campaign', fn ($q) => $q->where('status', 'active'));
    }

    public function publicImageUrl(): ?string
    {
        if (! filled($this->image_path) && ! filled($this->image_url)) {
            return null;
        }

        $base = rtrim((string) config('leadsmanager.api_base_url'), '/');
        if ($base === '') {
            return $this->image_url;
        }

        return "{$base}/api/ads/image/{$this->public_key}";
    }
}
