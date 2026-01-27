<?php namespace Pbs\Campaign\Components;

use Cms\Classes\ComponentBase;
use Pbs\Campaign\Models\Subscriber;
use Pbs\Campaign\Models\MailingList;

class Confirm extends ComponentBase
{
    public function componentDetails(): array
    {
        return [
            'name'        => 'Confirm Subscription',
            'description' => 'Handles the subscription confirmation link.'
        ];
    }

    public function onRun()
    {
        $hash = $this->param('hash');

        try {
            $subscriber = Subscriber::where('hash', $hash)
            ->where('status', 'pending')
            ->firstOrFail(); 

            $subscriber->update([
                'status' => 'active',
                'is_subscribed' => true,            
                'verified_at' => now(),
                'verification_token' => null,
            ]);            

            // Assign to mailing list based on locale
            $listName = "Website subscribers - " . ($subscriber->locale ?: 'en');
            // Create list if it doesn't exist
            $defaultList = MailingList::firstOrCreate(['name' => $listName]);
            
            // Sync without detaching to prevent duplicates if confirmed multiple times
            $subscriber->lists()->syncWithoutDetaching([$defaultList->id]);

            $this->page['subscriber'] = $subscriber;
            $this->page['success'] = true;
        } catch (\Exception $e) {
            \Log::error("[Campaign] Email verification failed for hash: {$hash}, error: {$e->getMessage()}");
            $this->page['success'] = false;
        }
    }
}
