<?php namespace Pbs\Campaign\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Pbs\Campaign\Models\Campaign;
use Pbs\Campaign\Models\Click;

class Clicks extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];    

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();        
        BackendMenu::setContext('Pbs.Campaign', 'campaign', 'clicks');
        
        // $this->addCss('/plugins/pbs/campaign/assets/css/campaign.css');
    }

    // don't forget to create the views, or: 
    // "Constant LARAVEL_START already defined"

    public function index()
    {
        $this->makeLists();
        $this->vars['clicks'] = Click::count();
        $listWidget = $this->asExtension('ListController')->listGetWidget();
        $query = $listWidget->prepareQuery();
        $this->vars['filteredClicks'] = $query->count();
        $this->asExtension('ListController')->index();
    }

    /**
     * Count total + filtered records.
     * not used
     */
    protected function getListCounts($listWidget)
    {
        $modelClass = $listWidget->model;
        $model = new $modelClass;

        // --- TOTAL
        $total = $model->newQuery()->count();

        // --- FILTERED
        $filteredQuery = $model->newQuery();

        // Apply listExtendQueryBefore (so filters or relations are respected)
        if (method_exists($this, 'listExtendQueryBefore')) {
            $this->listExtendQueryBefore($filteredQuery);
        }

        // Apply filters from Filter widget
        if (isset($this->filter) && method_exists($this->filter, 'applyAllScopesToQuery')) {
            $this->filter->applyAllScopesToQuery($filteredQuery);
        }

        // Apply search term if used
        $searchTerm = post('search');
        if ($searchTerm && method_exists($listWidget, 'applySearchToQuery')) {
            $listWidget->applySearchToQuery($filteredQuery, trim($searchTerm));
        }

        $filtered = $filteredQuery->count();

        return [
            'total' => $total,
            'filtered' => $filtered,
        ];
    }
    
    // not used
    public function onRefreshRecordCounts()
    {
        $listWidget = $this->listGetWidget();

        return [
            '#clicksCounter' => $this->makePartial('clicks_counter', [
                'counts' => $this->getListCounts($listWidget),
            ]),
        ];
    }

    // found on https://wintertricks.com/tricks/apply-a-default-sort-configuration-based-on-a-filter-scope-value
    // after 2 hours being fooled by AI..
    public function onGetFilteredRecords()
    {
        $this->makeLists();
        $listWidget = $this->asExtension('ListController')->listGetWidget();

        $query = $listWidget->prepareQuery();
        // is campaign needed?
        $query->with('link.campaign');
        $records = $query->get();

        // do something with $records
        // dd($records);
        // return $records;
        return [
            '#clicksCounter' => $this->makePartial('clicks_counter', [
                'counts' => Click::count(),
                'filteredClicks' => $records->count()
            ]),
        ];
    }
}
