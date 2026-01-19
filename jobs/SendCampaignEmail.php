<?php namespace Pbs\Campaign\Jobs;

use Mail;
use Pbs\Campaign\Models\Recipient;
use Pbs\Campaign\Models\Campaign;
use Pbs\Campaign\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Pbs\Campaign\Enums\CampaignStatus;
use Pbs\Campaign\Enums\CampaignRecipientStatus;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // don't forget to declare the properties!
    // don't forget to declare the properties!
    // don't forget to declare the properties!
    // don't forget to declare the properties!
    public $recipient;
    public $campaign;
    public $totalRecipients;
    public $currentRecipient;
    public $newsletter;

    // this used to receive a instance of Recipient
    // might be a good approach..
    // yes - and the same for Campaign: DONE
    // and maybe the newsletter too, as it will be needed
    // so instead of getting it here N times, get it once at higher level
    public function __construct(
        Recipient $recipient, 
        Campaign $campaign, 
        Newsletter $newsletter,
        $totalRecipients, 
        $currentRecipient)
    {
        // why this blows up..? was missing the id column, which is required by Laravel's Eloquent ORM
        // a job is serialized, and the ID is required
        $this->recipient = $recipient;
        $this->campaign = $campaign;

        // $this->newsletter = (object) $newsletter->toArray();// $newsletter;
        // break the connection to the model
        // why the cast? NOK
        /*
        $this->newsletter = (object) [
            'title' => $newsletter->title,
            'content' => $newsletter->content,
        ];  
        */
        // the hack above was NOK, and seems not needed at all (do some tests to confirm)
        // $this->newsletter = $newsletter;
        // model replication is needed here, so let's debug
        Log::info("original newsletter structure: " . $newsletter);
        
        Log::info("original newsletter structure: " . print_r($newsletter, true));
        $this->newsletter = (object) [
            'title' => $newsletter->title,
            // 'content' => [$newsletter->content], // Store as JSON string
            // 'content' => [json_encode($newsletter->content)], 
            'content' => $newsletter->content,
        ];
        
        $this->totalRecipients = $totalRecipients;
        $this->currentRecipient = $currentRecipient;

        // this runs everytime, so pass the newsletter already built
        // and just replace the recipient
        Log::info("SendCampaignEmail constructor called");
        // Log::info($this->newsletter->content);
    }

    // handle or fire?
    public function handle()
    {
        Log::info('Processing subscriber ' . $this->currentRecipient. ' of ' . $this->totalRecipients. ' with new newsletter: ' . print_r($this->newsletter, true));

        if (!$this->recipient) {
            return;
        }

        if ($this->campaign->status === CampaignStatus::Cancelled) {
            Log::info('Campaign canceled, aborting');
            return;
        }

        // WTF getting another version of the newsletter?
        // queued jobs are serialized.
        // when handle() begins, Laravel re-hydrates the model fresh from the database..
        // \Log::info('Class: ' . get_class($this));
        // \Log::info('Properties: ' . json_encode(get_object_vars($this)));

        // add the name, maybe the hash too instead of twig?

        $content = $this->newsletter->content ?? [];

        // add recipient name
        if (is_array($content)) {
            foreach ($content as $key => $item) {
                if (!empty($item['text']) && is_string($item['text'])) {
                    try {
                        $content[$key]['text'] = \Twig::parse($item['text'], [
                            'name' => $this->recipient->name,                            
                        ]);
                    } catch (\Exception $e) {
                        $content[$key]['body'] = $item['body'];
                    }
                }
            }
        }
        
        $this->newsletter->content = $content;

        $this->newsletter->beacon = url("/campaign/open/{$this->campaign->id}/{$this->recipient->hash}");

        $this->newsletter->unsubscribe = url("/campaign/unsubscribe/{$this->recipient->hash}");

        Log::info("newsletter content at send campaign email");
        Log::info($this->newsletter->content);

        // print_r($this->newsletter->content);
        // die();

        // TODO: 
        // - define the correct template and content
        // - handle links tracking, how? process links once at higher level (beforeCreate newsletter), and add tracking here?
        // template OK
        // links now just need the recipient hash (that should come from the subscriber)
        try {
            $unsubscribeUrl = url("/campaign/unsubscribe/{$this->recipient->hash}");

            Mail::send('pbs.campaign::newsletter.index', [
                'newsletter' => $this->newsletter,
                'recipient_hash' => $this->recipient->hash,
                'unsubscribeUrl' => $unsubscribeUrl,
            ], function($message) use ($unsubscribeUrl) {
                $message->to($this->recipient->email);
                $message->subject($this->campaign->subject);
                // Add List-Unsubscribe headers
                $headers = $message->getHeaders();
                $headers->addTextHeader('List-Unsubscribe', "<$unsubscribeUrl>");
                // Optional: For one-click unsubscribe (requires POST route handling)
                // $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            });

            $this->recipient->status = CampaignRecipientStatus::Sent;

        } catch (\Exception $e) {
            $this->recipient->status = CampaignRecipientStatus::Failed;
            Log::error('Error sending email to ' . $this->recipient->email . ': ' . $e->getMessage());
        }

        $this->recipient->save();

        if ($this->currentRecipient + 1 == $this->totalRecipients) {
            Log::info('Campaign ' . $this->campaign->id . ' is sent');            
            $this->campaign->status = CampaignStatus::Sent;
            $this->campaign->save();
        }
    }
}
