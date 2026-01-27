<?php namespace Pbs\Campaign\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Subscribers extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ImportExportController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Pbs.Campaign', 'campaign', 'subscribers');
    }

    public function import()
    {
        $this->pageTitle = 'Import Subscribers';
        // $this->addJs('/plugins/pbs/campaign/assets/js/importer.js');
        return $this->asExtension('ImportExportController')->import();
    }

    /*
    public function formExtendFields($form)
    {
        $model = $form->model;

        // If model is subscribed, hide unsubscribed_by
        if ($model->subscribed) {
            $form->removeField('unsubscribed_by');
            $form->removeField('unsubscribed_at');
        }
    }
    */

    /*
    public function update_onSave($recordId)
    {
        // save record
        parent::update_onSave($recordId);

        // get form widget after saving
        $widget = $this->asExtension('FormController')->formGetWidget();

        // re-render the form
        return [
            '#Form' => $widget->render()
        ];
    }
    */

    // Will bounce delete the subscriber?
    // No, just set the status to bounced
    // TODO: secure using webhook signature
    // classifying as nice to have for now, mailgun integration will be done later..
    public function bounce()
    {
        /*
        // reactivate subscribers
        $s = \Pbs\Campaign\Models\Subscriber::where('status', 'bounced')->first();
        $s->status = 'active';
        $s->save();
        return response('ok', 200);
        */
        
        $email = post('event-data.recipient');   // read once
        if (!$email) {
            \Log::warning('Bounce webhook missing recipient.');
            return response('missing recipient', 200);
        }

        try {            
            $subscriber = \Pbs\Campaign\Models\Subscriber::where('status', 'active')
                ->where('email', $email)
                ->first();

            if (!$subscriber) {
                \Log::info("[Campaign] Bounce received but subscriber not found or already inactive: {$email}");
                return response("{$email} not found", 200);
            }

            // guess that bounce might be a temporary thing, mailbox full..
            // how does mailgun know if it was a permanent bounce?
            $subscriber->status = 'bounced';
            $subscriber->subscribed = false;
            $subscriber->unsubscribed_at = now();
            $subscriber->unsubscribed_by = 'system';
            $subscriber->save();

            \Log::info("[Campaign] Subscriber marked as bounced: {$email}");
            return response('ok', 200);

        } catch (\Throwable $e) {
            // Add exception detail so you know WHY it failed
            \Log::error("[Campaign] Bounce error for {$email}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Still return 200 so Mailgun does not retry
            return response('fail', 200);
        }
    }
}
