<?php namespace Pbs\Campaign\Classes;

use Event;
use Log;
use Cookie;
use Pbs\Campaign\Models\Lead;

class EventHandlers
{
    public function subscribe($events)
    {
        // to trigger a event, use Event::fire('pbs.campaign.lead', [$campaign_id, $subscriber_id, $link_id]);
        // the data must be extracted from the cookie
        // TODO: write some docs on how this works
        // looks like the cookie is set when user clicks a link (Links.php)
        $events->listen('pbs.campaign.lead', [$this, 'onCampaignLead']);
    }

    public function onCampaignLead()
    {
        $data = json_decode(Cookie::get('campaign'), true);

        if (!$data) {
            return;
        }

        // register the lead
        // + merges two arrays, $data from cookie and the meta data
        Lead::create($data + [
            'meta' => json_encode([
                'lead_id' => 1,
                'lead_type' => 'reservation',
                'user_agent' => request()->userAgent(),                
            ]),
        ]);

        // after registering the lead, delete the cookie        
        Cookie::queue(Cookie::forget('campaign'));
    }
}
