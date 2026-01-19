<?php namespace Pbs\Campaign\Models;

use Model;

class Campaign extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'pbs_campaign_campaigns';

    protected $fillable = [
        'name',
        'subject',
        'newsletter_id',
        'description',
        'scheduled_at',
        'sent_at',
        'status'
    ];

    protected $dates = [
        'scheduled_at',
        'sent_at'
    ];

    public $rules = [
        'name' => 'required',
        'subject' => 'required',
        'newsletter_id' => 'required',
        'lists' => 'required'
    ];

    public $belongsTo = [
        'newsletter' => [
            'Pbs\Campaign\Models\Newsletter',
            'scope' => 'isFinished'
        ]
    ];

    public $belongsToMany = [
        'lists' => [
            'Pbs\Campaign\Models\MailingList',
            'table' => 'pbs_campaign_campaign_list',
            'key' => 'campaign_id',
            'otherKey' => 'list_id',
            'scope' => 'hasSubscribers'
        ]
    ];

    public $hasManyThrough = [
        'clicks' => [
            'Pbs\Campaign\Models\Click',
            'through' => 'Pbs\Campaign\Models\Link',
            'key' => 'campaign_id',          // Foreign key on campaign_links
            'throughKey' => 'link_id' // Foreign key on campaign_clicks
        ]
    ];

    public $hasMany = [
        'recipients' => Recipient::class,
        'links' => Link::class,
        'visualizations' => Visualization::class,
        'leads' => Lead::class,
    ];

    // not used?
    /*
    public function getMailingListOptions()
    {
        $lists = \Pbs\Campaign\Models\MailingList::all();
        $options = [];

        foreach ($lists as $list) {
            $subscriberCount = $list->subscribers()->count();
            $options[$list->id] = sprintf(
                '%s (%d subscribers) - %s',
                $list->name,
                $subscriberCount,
                $list->description
            );
        }

        return $options;
    }
    */

    /**
     * Get the total number of clicks for this campaign
     * @return int
     * is this used? yes.
     */
    public function getClicksCountAttribute()
    {
        return $this->links()->withCount('clicks')->get()->sum('clicks_count');
    }    

    public function getVisualizationsCountAttribute()
    {
        return $this->visualizations()->count();
    }

    public function getLeadsCountAttribute()
    {
        return $this->leads()->count();
    }

    public function beforeDelete()
    {
        # delete all recipients
        $this->recipients()->delete();
    }

    public function getIdAndNameAttribute()
    {
        return $this->id . '- ' . $this->name;
    }
}