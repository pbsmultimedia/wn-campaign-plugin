<?php namespace Pbs\Campaign\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'pbs_campaign_settings';
    public $settingsFields = 'fields.yaml';

    protected $cache = [];

    public $settingsDefaultValues = [
        'daily_limit' => 100,
    ];    

    /*
    public function getMailDriverOptions()
    {
        $drivers = [];

        // 1. Core Winter driver
        $defaultDriver = config('mail.default');
        if ($defaultDriver) {
            $drivers[$defaultDriver] = ucfirst($defaultDriver);
        }


        // 2. Extra mailers (SES plugin, Gmail plugin, etc)
        $mailers = config('mail.mailers', []);
        if (is_array($mailers)) {
            foreach ($mailers as $key => $cfg) {
                $drivers[$key] = ucfirst($key);
            }
        }

        return $drivers;
    }
    */
}