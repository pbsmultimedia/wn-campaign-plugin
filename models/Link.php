<?php namespace Pbs\Campaign\Models;

use Model;

/**
 * Link Model
 */
class Link extends Model
{
    // use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'pbs_campaign_links';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'campaign_id',
        'original_url',
        'hash'
    ];

    /**
     * @var array Validation rules
     */
    /*
    public $rules = [
        'campaign_id' => 'required|exists:pbs_campaign_campaigns,id',
        'original_url' => 'required|url',
    ];
    */

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'campaign' => [Campaign::class],
    ];

    public $hasMany = [
        'clicks' => [Click::class],
    ];

    /**
     * Get the total number of clicks for this link
     *
     * @return int
     */
    public function getTotalClicksAttribute()
    {
        return $this->clicks()->count();
    }

    /**
     * Get the unique number of recipients who clicked this link
     *
     * @return int
     */
    public function getUniqueClicksAttribute()
    {
        return $this->clicks()
            ->distinct('recipient_id')
            ->count('recipient_id');
    }

    /**
     * Get the click-through rate for this link
     *
     * @param int $totalRecipients Total number of recipients in the campaign
     * @return float
     */
    public function getClickThroughRateAttribute($totalRecipients)
    {
        if ($totalRecipients <= 0) {
            return 0;
        }
        
        return ($this->total_clicks / $totalRecipients) * 100;
    }

    /**
     * Record a click on this link
     *
     * @param int $recipientId
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return Click
     */
    public function recordClick($recipientId, $ipAddress = null, $userAgent = null)
    {
        $this->increment('clicks');
        
        return Click::create([
            'link_id' => $this->id,
            'recipient_id' => $recipientId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    }

    /**
     * Scope to get links for a specific campaign
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $campaignId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCampaign($query, $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope to get the most clicked links
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMostClicked($query, $limit = 10)
    {
        return $query->orderBy('clicks', 'desc')->take($limit);
    }

    /**
     * Get the domain from the original URL
     *
     * @return string
     */
    public function getDomainAttribute()
    {
        $parsed = parse_url($this->original_url);
        return $parsed['host'] ?? '';
    }

    // too much boilerplate above..
}
