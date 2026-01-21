<?php namespace Pbs\Campaign\Classes;

use BackendAuth;
use Event;
use Mail;
use Log;
use Twig;
use Pbs\Campaign\Models\Newsletter;
use Pbs\Campaign\Models\Subscriber;
use Pbs\Campaign\Enums\NewsletterStatus;
use Pbs\Campaign\Models\Campaign;

class NewsletterPreviewService
{
    // backend preview
    public function generatePreviewHtml($newsletter_id = null, $campaign_id = null)
    {
        // Load or create newsletter
        $newsletter = $newsletter_id ? Newsletter::find($newsletter_id) : new Newsletter();

        $subject = null;
        if ($campaign_id) {
            $campaign = Campaign::find($campaign_id);
            $subject = $campaign->subject;
        }

        if ($newsletter->status !== 'finished') {
            $newsletter->fill(post('Newsletter') ?? []);
        }

        // Parse content
        $content = $newsletter->content ?? [];

        // fancy stuff: show admin name in preview instead of placeholder
        // guess this is used else where..? - SendCampaignEmail - DRY..!
        // blows up on wintercm 1.1.9 and PHP 8.0.16
        /*
        if (is_array($content)) {
            foreach ($content as $key => $item) {
                if (!empty($item['text']) && is_string($item['text'])) {
                    try {
                        $content[$key]['text'] = Twig::parse($item['text'], [
                            'name' => BackendAuth::getUser()->first_name,
                        ]);
                    } catch (\Exception $e) {
                        $content[$key]['text'] = $item['text'];
                    }
                }
            }
        }
        */        

        if (is_array($content)) {
            foreach ($content as $key => $item) {
                if (!empty($item['text']) && is_string($item['text'])) {
                    // Simple string replacement, not Twig parsing
                    $content[$key]['text'] = str_replace(
                        '{{name}}',
                        BackendAuth::getUser()->first_name,
                        $item['text']
                    );
                }
            }
        }

        $newsletter->content = $content;

        // testing preview text        
        /*
        $newsletter->preview = collect($content)
            ->first(function($item) {
                return !empty($item['text']) && is_string($item['text']);
            })['text'] ?? null;
        */

        $previewText = \Illuminate\Support\Str::limit(
            collect($content)
                ->pluck('text')
                ->filter(function($text) {
                    return !empty($text) && is_string($text);
                })
                ->map(function($text) {
                    return html_entity_decode(strip_tags($text)); // Decode entities
                })
                ->implode(' '),
            150,
            '...'
        );

        $newsletter->preview = $previewText;
                
        // Clean: strip tags, remove newlines, truncate
        $newsletter->preview = strip_tags($newsletter->preview);
        $newsletter->preview = str_replace(["\r", "\n"], ' ', $newsletter->preview);
        // Also apply the limit here for consistency, though visual preview might tolerate more
        $newsletter->preview = \Str::limit($newsletter->preview, 150);        

        // Capture HTML email output
        $html = '';
        Event::listen('mailer.prepareSend', function ($mailerInstance, $view, $message) use (&$html) {
            try {
                $html = (string) $message->getHtmlBody();
            } catch (\Throwable $e) {
                $html = (string) $message->getBody();
            }
            return false; // stop actual send
        });

        Mail::send($newsletter->template, [
            'newsletter' => $newsletter,            
        ], function ($message) use ($newsletter, $subject) {
            $message->subject($subject ?? 'Subject will be defined on campaign creation');
        });

        // Return iframe
        // style should be applied to the outter element because of refresh
        return "<iframe style=\"width:99%; height:80vh; border:1px solid #ccc; border-radius:8px; background-color:#fff; box-shadow:4px 4px 0 rgba(0,0,0,0.1);\" srcdoc=\"" . htmlspecialchars($html, ENT_QUOTES) . "\"></iframe>";
    }

    // frontend preview
    public function render($id, $hash)
    {
        // booleans: is_something 
        // https://wintercms.com/docs/develop/docs/architecture/developer-guide
        try {
            $newsletter = Newsletter::where('is_public', true)->where('status', NewsletterStatus::Finished)->findOrFail($id);
        } catch (\Exception $e) {
            return 'Newsletter not found';
        }

        // check if newsletter is private and hash matches
        if (!$newsletter->is_public && !Subscriber::where('hash', $hash)->exists()) {
            return 'Newsletter not found';
        }

        // TODO: 
        // - track links (if hash exists) tricky, marking as nice to have..
        // needs a bit of reverse engineering to get the campaign links
        // first get the normal links from unparsed content
        // then, using campaign ID, get the campaign links (guess there is issue, because hashed links use random hash?)
        // then replace the links in the content and add the recipient hash
        // - if newsletter is private, check if hash matches

        // Capture HTML email output
        $html = '';
        Event::listen('mailer.prepareSend', function ($mailerInstance, $view, $message) use (&$html) {
            try {
                $html = (string) $message->getHtmlBody();
            } catch (\Throwable $e) {
                $html = (string) $message->getBody();
            }
            return false; // stop actual send
        });        

        // template - hardcoded or setting?
        Mail::send('pbs.campaign::newsletter.index', [
            'newsletter' => $newsletter,
        ]);

        return $html;
    }
}
