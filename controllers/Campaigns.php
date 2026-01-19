<?php namespace Pbs\Campaign\Controllers;

use Flash;
use Log;
use BackendMenu;
use Redirect;
use Backend\Classes\Controller;
use Pbs\Campaign\Models\MailingList;
use Pbs\Campaign\Models\Recipient;
use Pbs\Campaign\Jobs\SendCampaignEmail;
use Pbs\Campaign\Models\Campaign;
use Pbs\Campaign\Models\Newsletter;
use Pbs\Campaign\Classes\NewsletterPreviewService;
use Pbs\Campaign\Enums\CampaignStatus;

class Campaigns extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\RelationController::class
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Pbs.Campaign', 'campaign', 'campaigns');
    }

    // each campaign must only be sent once
    // so campaign ID is needed
    public function onSend()
    {
        $campaign_id = post('campaign_id');
        $campaign = Campaign::find($campaign_id);
        $campaign->status = 'sending';
        $campaign->save();

        // get the emails from selected mailing lists
        // (randomly did not post? looks OK now)
        $lists = post('lists');

        $emails = MailingList::whereIn('id', $lists)
            ->with(['subscribers' => function ($q) {
                $q->where('is_subscribed', true);
            }])
            ->get()
            ->pluck('subscribers')
            ->flatten()
            ->pluck('id')
            ->unique();

        // there is a easier way to do this - don't remember how.. xdebug or debugbar?
        // print_r($emails);

        $totalRecipients = $emails->count();

        // store each email in a DB table
        // with campaign_id, newsletter_id
        $i = 0;

        // FIXME: DEV only..!
        // deletes all recipients
        // Recipient::query()->delete();

        $newsletter = Newsletter::find(post('Campaign')['newsletter']);

        // does this return a model? yes.
        // having some issues with the job / hydrating the model
        // maybe process newsletter and save it? it won't work, because campaing id is needed..
        $newsletterProcessed = $newsletter->processAndTrackLinks($newsletter, $campaign->id);

        $daily_limit = \Pbs\Campaign\Models\Settings::get('daily_limit');
        // 86400 seconds = 1 day
        $delay = 86400 / $daily_limit;

        // nice to have: adjust delay based on emails sent on that day
        // so that if the limit is low, and recipients are few, it doesn't take too long
        // one can send just a newsletter for 10 recipients in a day
        // so the delay should be 86400 / 10 = 8640 seconds, which is 2 hours..

        foreach ($emails as $email) {
            $recipient = new Recipient();
            $recipient->campaign_id = post('campaign_id');
            $recipient->newsletter_id = $newsletter->id;
            // fishy.. rename this later
            $recipient->subscriber_id = $email;
            $recipient->status = 'pending';
            $recipient->save();

            SendCampaignEmail::dispatch(
                $recipient,
                $campaign,
                $newsletterProcessed,
                $totalRecipients, 
                $i
            )->delay(now()->addSeconds($delay));

            $i++;
        }

        // TODO: mark the campaign as sent, redirect and show a message - done (?)
        // looks like it's working, but this message is too long to use in a flash message..
        // flash and redirect after
        Flash::success("Action completed successfully. Sending $totalRecipients emails, with a delay of $delay seconds between each email. It will take around " . ($totalRecipients * $delay) / 3600 . " hours to finish.");
        return Redirect::back();
    }

    // it seems that be default a ajax handler receives the id of the current model
    // had some issues with that.. is this still used? yes, to preview the newsletter while creating a campaign
    public function onPreview()
    {
        $newsletter = (new NewsletterPreviewService())->generatePreviewHtml(post('Campaign')['newsletter']);
        
        return '
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="popup">&times;</button>
                <h4 class="modal-title">Preview</h4>
            </div>
            <div class="modal-body">
                ' . $newsletter . '
            </div>            
        ';
    }

    public function update($id = null)
    {
        $model = $this->asExtension('FormController')->formFindModelObject($id);
        $this->vars['preview'] = (new NewsletterPreviewService())->generatePreviewHtml($model->newsletter_id, $id);
        $this->vars['totalRecipients'] = $model->recipients()->count();
        $this->vars['sentRecipients'] = $model->recipients()->where('status', '!=', 'pending')->count();
        return $this->asExtension('FormController')->update($id);
    }

    // refresh the recipients list
    public function onRefresh($id)
    {
        $campaign = Campaign::find($id);

        // Tell the relation behavior which model to use
        $this->initRelation($campaign);

        // Progress info
        $totalRecipients = $campaign->recipients()->count();
        $sentRecipients = $campaign->recipients()->where('status', '!=', 'pending')->count();

        // Now render the relation (passing the progress info - why?)
        $html = $this->relationRender('recipients');

        return [
            '#recipients'      => $html,
            '#sentRecipients'  => $sentRecipients,
            '#totalRecipients' => $totalRecipients,
        ];
    }


    // disable fields an relations if already sent
    public function formExtendFields($form)
    {
        $status = $form->model->status;
        $fields = $form->getFields();

        foreach ($fields as $name => $field) {
            if ($form->getContext() === 'update' && $status !== 'draft') {
                $field->readOnly = true;
            }

            if ($status === 'draft' && in_array($field->tab, ['Metrics', 'Progress', 'Visualizations', 'Leads', 'Recipients', 'Metrics'])) {
                $form->removeField($name);
            }
        }
    }

    // amazing!
    public function relationExtendConfig($config, $field, $model)
    {
        if ($model->status && $model->status !== 'draft') {
            $config->view['toolbarButtons'] = false;
            $config->manage['showSearch'] = false;
            $config->view['showCheckboxes'] = false;
        }
    }

    public function onCancel()
    {
        $campaign = Campaign::find(post('campaign_id'));
        $campaign->status = CampaignStatus::Cancelled->value;
        $campaign->save();
        Flash::success('Campaign cancelled successfully.');
    }
}