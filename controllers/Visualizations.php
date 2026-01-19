<?php namespace Pbs\Campaign\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Pbs\Campaign\Models\Visualization;

class Visualizations extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
    ];

    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Pbs.Campaign', 'campaign', 'visualizations');
    }
    
    public function index()
    {
        $this->makeLists();
        $this->vars['visualizations'] = Visualization::count();
        $listWidget = $this->asExtension('ListController')->listGetWidget();
        $query = $listWidget->prepareQuery();
        $this->vars['filteredVisualizations'] = $query->count();
        $this->asExtension('ListController')->index();
    }

    public function onGetFilteredRecords()
    {
        $this->makeLists();
        $listWidget = $this->asExtension('ListController')->listGetWidget();

        $query = $listWidget->prepareQuery();
        // is campaign needed?
        // $query->with('link.campaign');
        $records = $query->get();

        return [
            '#visualizationsCounter' => $this->makePartial('visualizations_counter', [
                'counts' => Visualization::count(),
                'filteredVisualizations' => $records->count()
            ]),
        ];
    }
}
