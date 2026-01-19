<?php namespace Vendor\Plugin\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

// is this needed? 
class Settings extends Controller
{
    /*
    public $implement = [
        'Backend.Behaviors.FormController',
    ];

    public $formConfig = 'config_form.yaml';
    */

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Vendor.Plugin', 'plugin', 'settings');
    }    
}
