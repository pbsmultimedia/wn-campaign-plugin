<?php namespace Pbs\Campaign\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Lists extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Pbs.Campaign', 'campaign', 'lists');
    }
}
