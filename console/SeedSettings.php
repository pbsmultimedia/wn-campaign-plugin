<?php namespace Pbs\Campaign\Console;

use Winter\Storm\Console\Command;
use Pbs\Campaign\Models\Settings as Settings;

class SeedSettings extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'campaign:settings';

    /**
     * The console command description.
     */
    protected $description = 'Seed default settings for the plugin.';

    public function handle()
    {
        $settings = new Settings();
        $defaults = $settings->settingsDefaultValues ?? [];

        if (empty($defaults)) {
            $this->info('No default settings defined.');
            return 0;
        }

        foreach ($defaults as $key => $value) {
            // Only set if not already set
            if (Settings::get($key) === null) {
                Settings::set($key, $value);
                $this->info("Setting [$key] => " . json_encode($value));
            }
        }

        $this->info('Default settings seeded successfully.');
        return 0;
    }
}
