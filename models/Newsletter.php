<?php namespace Pbs\Campaign\Models;

use Model;
use Url;
use Log;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Models\MailTemplate;
use Winter\Translate\Models\Locale;
use Pbs\Campaign\Enums\NewsletterStatus;

class Newsletter extends Model
{
    public $table = 'pbs_campaign_newsletters';

    protected $fillable = [
        'title',
        'subject',
        'content',
        'status',
        'template',
    ];

    public $hasMany = [
        'campaign' => 'Pbs\Campaign\Models\Campaign'
    ];

    protected $jsonable = ['content'];

    // TODO: 
    // - list pages from static pages
    // - list dynamic pages from cms pages (point the page and DB table / slug)
    // it's a nice to have.. but guess it won't be part of v1.0
    /*
    public function getPagesOptions()
    {
        $pages = Page::listInTheme(Theme::getActiveTheme());
        $options = [];

        foreach ($pages as $page) {
            $options[Url::to($page->url)] = $page->title ?: $page->url;
        }

        return $options;
    }
    */

    public function scopeIsFinished($query)
    {
        return $query->where('status', NewsletterStatus::Finished);
    }

    // process links outside the send loop
    // this can happen also just on memory at Campaign level
    // just testing for now
    // working at campaign level is better, because campaign id is needed
    // maybe this could be a service?
    // logic was NOK and it was flattening the content array..
    // spent some hours on this, and it was solve Antigravity / Gemini
    // thought the issue was with the newsletter model copy and got funnel vision
    public function processAndTrackLinks($newsletter, $campaign_id)
    {
        if (!is_array($newsletter->content)) {
            return $newsletter;
        }

        $newContent = [];

        foreach ($newsletter->content as $block) {
            if (is_array($block)) {
                $newBlock = $block;
                foreach ($block as $key => $value) {
                    // Only process strings for links
                    if (is_string($value)) {
                        $pattern = '/\b(https?:\/\/[a-z0-9\-._~:\/?#[\]@!$&\'()*+,;=]+)/i';
                        
                        // Replace each URL with a tracking URL
                        $result = preg_replace_callback($pattern, function($matches) use ($campaign_id) {
                            $url = $matches[1];
                            
                            $hash = md5($campaign_id . '|' . $url);

                            // store the original link and the hash in a Link model
                            Link::firstOrCreate([                            
                                'campaign_id' => $campaign_id,
                                'hash' => $hash                            
                            ], [
                                'original_url' => $url,
                            ]);

                            return Url::to("/campaign/link/$hash")."/{{recipient_hash}}";
                        }, $value);

                        $newBlock[$key] = $result;
                    }
                }
                $newContent[] = $newBlock;
            } else {
                // Keep non-array blocks as is (though they should be arrays)
                $newContent[] = $block;
            }
        }

        $newsletter->content = $newContent;
        return $newsletter;
    }

    // beacon setter and getter
    public function setBeaconAttribute($value)
    {
        $this->attributes['beacon'] = $value;
    }

    public function getBeaconAttribute()
    {
        // If you already set it manually, return it
        if (isset($this->attributes['beacon'])) {
            return $this->attributes['beacon'];
        }

        // Otherwise, compute dynamically
        // Replace with real campaign/recipient logic if needed
        if (isset($this->campaign) && isset($this->recipient)) {
            return url("/campaign/open/{$this->campaign->id}/{$this->recipient->hash}");
        }

        // fallback
        return null;
    }

    public static function getTemplateOptions()
    {
        return MailTemplate::where('description', 'LIKE', '%newsletter%')
            ->pluck('code', 'code')
            ->toArray();
    }

    public function getLocaleOptions()
    {
        return Locale::listEnabled();
    }
}

