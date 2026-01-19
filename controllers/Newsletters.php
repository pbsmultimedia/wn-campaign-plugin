<?php namespace Pbs\Campaign\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Models\MailTemplate;
use Pbs\Campaign\Models\Newsletter;
use Pbs\Campaign\Models\MailingList;
use Pbs\Campaign\Classes\NewsletterPreviewService;
use Mail;
use Carbon\Carbon;
use Backend;
use Flash;
use View;
use Log;
use Event;
use Pbs\Campaign\Enums\NewsletterStatus;

class Newsletters extends Controller
{
    public $requiredPermissions = ['pbs.campaign.manage'];

    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];

    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Pbs.Campaign', 'campaign', 'newsletters');
    }

    public function update($id = null)
    {
        $this->addJs('$/pbs/campaign/assets/js/insertname.js');
        // $this->addJs('$/pbs/campaign/assets/js/emoji.js');
        $this->addJs('$/pbs/campaign/assets/js/tracker.js');
        return $this->asExtension('FormController')->update($id);
    }

    // disable fields an relations if already sent
    public function formExtendFields($form)
    {
        if ($form->model && $form->model->status === NewsletterStatus::Finished) {
            foreach ($form->getFields() as $field) {
                $field->readOnly = true;
                // this did the trick on repeater fields!
                // disabled fields are not posted..
                // $field->disabled = true;
            }
        }
    }

    // no relations here?
    public function relationExtendConfig($config, $field, $model)
    {
        if ($model->status === 'finished') {
            $config->view['toolbarButtons'] = false;
            $config->manage['showSearch'] = false;
            $config->view['showCheckboxes'] = false;
        }
    }

    public function onPreview($id = null)
    {
        return (new NewsletterPreviewService())->generatePreviewHtml($id);
    }

    public function onFinish($id = null)
    {
        $newsletter = $id ? Newsletter::find($id) : new Newsletter();
        $newsletter->fill(post('Newsletter'));
        $newsletter->status = NewsletterStatus::Finished;
        $newsletter->save();
        
        Flash::success('Newsletter has been finished! Create a campaign to send it.');
        return \Redirect::refresh();
    }

    /**
     * Override the record URL to be dynamic based on status
     */
    public function listGetRecordUrl($record)
    {
        if ($record->status === 'finished') {
            // Finished newsletters go to preview (read-only)
            return Backend::url('pbs/campaign/newsletters/preview/' . $record->id);
        } else {
            // Draft/editable newsletters go to update page
            return Backend::url('pbs/campaign/newsletters/update/' . $record->id);
        }
    }

    // nice to know, but not used!
    public function test()
    {
        return \View::make('pbs.campaign::filter');
    }

    public function onLoadTemplateSelector()
    {
        $model = new \Pbs\Campaign\Models\Newsletter;

        $config = $this->makeConfig('$/pbs/campaign/models/newsletter/start-fields.yaml');
        $config->model = $model;
        $config->alias = 'templateSelectorForm';
        $config->arrayName = 'TemplateSelector';

        $widget = $this->makeWidget(\Backend\Widgets\Form::class, $config);
        $widget->bindToController();

        $this->vars['formWidget'] = $widget;

        return $this->makePartial('template_selector');
    }

    public function onSelectTemplate()
    {
        $data = post('TemplateSelector');
        $model = new \Pbs\Campaign\Models\Newsletter;
        $model->fill($data);
        $model->save();

        return \Backend::redirect('pbs/campaign/newsletters/update/' . $model->id);
    }
    
}