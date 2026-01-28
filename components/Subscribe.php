<?php namespace Pbs\Campaign\Components;

use Flash;
use Redirect;
use App;
use Cms\Classes\ComponentBase;
use Pbs\Campaign\Models\Subscriber;


class Subscribe extends ComponentBase
{
    public function componentDetails(): array
    {
        return [
            'name'        => 'Subscribe',
            'description' => 'Renders a form to subscribe to a newsletter.'
        ];
    }
    
    public function onSubscribe()
    {        
        $locale = App::getLocale();

        $subscriber = Subscriber::create([
            'name' => post('name'),
            'email' => post('email'),            
            'is_subscribed' => false,
            'status' => 'pending',
            'locale' => $locale,
            'ip' => request()->ip(),  // Handles proxies automatically            
            'source' => 'user',
            'consent_given_at' => now(),
            'consent_version' => '1.0', // Consent version could come from settings
        ]);

        // Send confirmation email
        $confirmLink = url('/' . $locale . '/campaign/confirm/' . $subscriber->hash);
        
        \Mail::send('pbs.campaign::newsletter.confirmation', ['confirm_link' => $confirmLink], function($message) use ($subscriber) {
            $message->to($subscriber->email, $subscriber->name);
        });

        return [
            '#subscribe-placeholder' => $this->renderPartial('@feedback')
        ];
    }
}
