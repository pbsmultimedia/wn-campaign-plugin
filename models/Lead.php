<?php namespace Pbs\Campaign\Models;

use Model;

class Lead extends Model
{
    public $table = 'pbs_campaign_leads';

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'link_id',
        'meta',
    ];

    protected $jsonable = ['meta'];

    public $belongsTo = [
        'campaign' => \Pbs\Campaign\Models\Campaign::class,
        'subscriber' => \Pbs\Campaign\Models\Subscriber::class,
        'link' => \Pbs\Campaign\Models\Link::class,
    ];

    // disable just updated_at
    const UPDATED_AT = null;
}
