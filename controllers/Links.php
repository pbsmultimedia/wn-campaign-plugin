<?php namespace Pbs\Campaign\Controllers;

use Backend\Classes\Controller;
use Pbs\Campaign\Models\Link;
use Pbs\Campaign\Models\Subscriber;
use Cookie;

class Links extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function track($link_hash, $recipient_hash)
    {
        $link = Link::where('hash', $link_hash)->firstOrFail();   
        $subscriber = Subscriber::where('hash', $recipient_hash)->firstOrFail();
        
        $link->clicks()->create([
            'subscriber_id' => $subscriber->id,
            'link_id' => $link->id,
            'user_agent' => request()->userAgent(),
        ]);

        // Create the cookie
        $cookie = Cookie::make(
            'campaign',            
            json_encode([
                'campaign_id' => $link->campaign_id,
                'subscriber_id' => $subscriber->id,
                'link_id' => $link->id,
            ]),            
            60 * 24 * 30,        // 30 days in minutes
            '/',                 // path
            null,                // domain = current domain
            false,               // secure = true if HTTPS
            false                // httpOnly (false if JS needs access)
        );

        // Redirect with the cookie attached
        return redirect($link->original_url)
            ->withCookie($cookie);
    }
}