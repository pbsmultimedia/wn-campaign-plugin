<?php namespace Pbs\Campaign\Models;

use Model;
use Log;

// why some models have the campaign prefix and some don't?
class Click extends Model
{
    protected $table = 'pbs_campaign_clicks';
    
    public $timestamps = false;
    
    // maybe remove this?
    protected $dates = [
        'first_clicked_at',
        'last_clicked_at'
    ];

    protected $fillable = [
        'link_id',
        'subscriber_id',
        'user_agent'
    ];
    
    public $belongsTo = [
        // naming things..
        // relation name should match the table name, so that the key is correct
        'link' => ['Pbs\Campaign\Models\Link', 'key' => 'link_id'],
        'subscriber' => ['Pbs\Campaign\Models\Subscriber', 'key' => 'subscriber_id']
    ];

    public function scopeFilterCampaigns($query, $scopes = null)
    {
        if (is_array($scopes)) {
            // why link is here?
            return $query->whereHas('link', function($q) use ($scopes) {
                $q->whereIn('campaign_id', (array) $scopes);
            });
        }
        return $query;
    }

    // not used ATM
    /*
    public function scopeFilterLinks($query, $linkId)
    {
        Log::info("Filtering by link ID");
        Log::info($linkId);
        return $query->where('campaign_link_id', $linkId);
    }
    */   
}