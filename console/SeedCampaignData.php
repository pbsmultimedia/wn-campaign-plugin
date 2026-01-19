<?php namespace Pbs\Campaign\Console;

use Illuminate\Console\Command;
use Pbs\Campaign\Models\Subscriber;
use Pbs\Campaign\Models\MailingList;
use Pbs\Campaign\Models\Newsletter;
use Pbs\Campaign\Models\Campaign;
use Pbs\Campaign\Enums\NewsletterStatus;

class SeedCampaignData extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'campaign:seed';

    /**
     * @var string The console command description.
     */
    protected $description = 'Seed the campaign database with sample data';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->info('Seeding campaign data...');
        
        // Your existing seeder logic here
        $this->seedSubscribers();
        $this->seedMailingLists();
        // $this->seedNewsletters();
        // NOK
        // $this->seedCampaigns();
        
        $this->info('Campaign data seeded successfully!');
    }

    protected function seedSubscribers()
    {
        $subscribers = [
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['name' => 'Bob Johnson', 'email' => 'bob@example.com'],
            ['name' => 'Alice Williams', 'email' => 'alice@example.com'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com'],
        ];

        foreach ($subscribers as $subscriber) {
            Subscriber::firstOrCreate(
                ['email' => $subscriber['email']],
                $subscriber
            );
        }
        $this->info('Subscribers seeded');
    }

    protected function seedMailingLists()
    {
        $lists = [
            ['name' => 'General Newsletter'],
            ['name' => 'Premium Members'],
            ['name' => 'Beta Testers'],
        ];

        foreach ($lists as $list) {
            MailingList::firstOrCreate($list);
        }
        $this->info('Mailing lists seeded');
    }

    protected function seedNewsletters()
    {
        $newsletters = [
            [
                'title' => 'Welcome to Our Newsletter',
                'content' => '<h1>Welcome to our newsletter!</h1><p>Thank you for subscribing to our newsletter.</p>',
                'status' => NewsletterStatus::Finished->value
            ],
            // ... rest of your newsletter data
        ];

        foreach ($newsletters as $newsletter) {
            Newsletter::firstOrCreate(
                ['title' => $newsletter['title']],
                $newsletter
            );
        }
        $this->info('Newsletters seeded');
    }

    protected function seedCampaigns()
    {
        // First, create the campaign without lists
        $campaigns = [
            [
                'name' => 'Welcome Campaign',
                'subject' => 'Welcome to Our Service',
                'newsletter_id' => 1,
                'description' => 'Welcome email for new subscribers',
                'status' => 'draft',
                'lists' => [1]
            ],
            [
                'name' => 'Monthly Newsletter - October 2025',
                'subject' => 'October 2025 Newsletter',
                'newsletter_id' => 2,
                'description' => 'Monthly newsletter for October 2025',
                'status' => 'scheduled',
                'scheduled_at' => now()->addDays(7),
                'lists' => [1]
            ]
        ];

        foreach ($campaigns as $campaignData) {
            // Campaign::create($campaignData);
            $campaing = new Campaign();
            // $campaing->fill($campaignData);
            $campaing->name = $campaignData['name'];
            $campaing->subject = $campaignData['subject'];
            $campaing->newsletter_id = $campaignData['newsletter_id'];
            $campaing->description = $campaignData['description'];
            $campaing->status = $campaignData['status'];
            $campaing->lists()->sync($campaignData['lists']);
            $campaing->save();

            $this->info("Campaign '{$campaign->name}' created with " . $campaign->lists()->count() . ' lists');
        }

        $this->info('All campaigns seeded successfully');
    }
}
