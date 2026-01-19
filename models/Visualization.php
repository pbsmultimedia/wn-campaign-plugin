<?php namespace Pbs\Campaign\Models;

use Model;

class Visualization extends Model
{
    public $table = 'pbs_campaign_visualizations';

    protected $fillable = [
        'subscriber_id',
        'campaign_id',
        'user_agent',
    ];

    public $belongsTo = [
        'campaign' => \Pbs\Campaign\Models\Campaign::class,
        'subscriber' => \Pbs\Campaign\Models\Subscriber::class,
    ];

    // disable updated_at
    const UPDATED_AT = null;

    public function scopeFilterCampaigns($query, $scopes = null)
    {
        if (is_array($scopes)) {
            return $query->whereHas('campaign', function($q) use ($scopes) {
                $q->whereIn('campaign_id', (array) $scopes);
            });
        }
        return $query;
    }
}
