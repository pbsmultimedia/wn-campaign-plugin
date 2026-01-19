<?php namespace Pbs\Campaign\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Pbs\Campaign\Models\Lead;

class Leads extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
    ];

    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Pbs.Campaign', 'campaign', 'leads');
    }
    public function index()
    {
        $this->makeLists();
        $this->vars['leads'] = Lead::count();
        $listWidget = $this->asExtension('ListController')->listGetWidget();
        $query = $listWidget->prepareQuery();
        $this->vars['filteredLeads'] = $query->count();
        $this->asExtension('ListController')->index();
    }

    public function onGetFilteredRecords()
    {
        $this->makeLists();
        $listWidget = $this->asExtension('ListController')->listGetWidget();

        $query = $listWidget->prepareQuery();
        $records = $query->get();

        return [
            '#leadsCounter' => $this->makePartial('leads_counter', [
                'counts' => Lead::count(),
                'filteredLeads' => $records->count()
            ]),
        ];
    }
}
