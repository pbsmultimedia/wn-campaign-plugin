<?php namespace Pbs\Campaign\Components;

use Cms\Classes\ComponentBase;
use Flash;
use Redirect;
use Pbs\Campaign\Models\Subscriber;

class Unsubscribe extends ComponentBase
{
    public function componentDetails(): array
    {
        return [
            'name'        => 'Unsubscribe',
            'description' => 'Renders a form to unsubscribe from a newsletter.'
        ];
    }

    /*
    public function onRun()
    {
        $this->page['hash'] = $this->param('hash');
    }
    */
    
    // to avoid "hacks", the user should confirm the action
    // then receive a email with a link to unsubscribe
    // or at least, confirm the email
    // classifying as nice to have
    public function onUnsubscribe()
    {
        try {
            $subscriber = Subscriber::where('hash', $this->param('hash'))->where('is_subscribed', true)->firstOrFail();
            // just for testing
            // $subscriber = Subscriber::where('hash', $this->param('hash'))->firstOrFail();
            $subscriber->is_subscribed = false;
            $subscriber->unsubscribed_at = now();
            $subscriber->unsubscribed_by = 'user';
            $subscriber->save();
            // Flash::success('Unsubscribed successfully.');
            \Log::info("Unsubscribed {$subscriber->email}");
            
            return [
                '#unsubscribe-placeholder' => $this->renderPartial('@feedback')
            ];
        } catch (\Exception $e) {
            // TODO: translate
            Flash::error('Subscriber not found or already unsubscribed.');
            \Log::error("Unsubscribed failed for hash {$this->param('hash')}: {$e->getMessage()}");
        }
    }
}
