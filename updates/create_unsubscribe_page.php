<?php namespace Pbs\Campaign\Updates;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Winter\Storm\Database\Updates\Seeder;

// maybe do the same for the newsletter page
// also should there be a archive?
// if so, newsletter should have a attribute to define if is public or not..
class SeedUnsubscribePage extends Seeder
{
    public function run()
    {
        // $theme = Theme::getActiveTheme()->getDirName();
        $theme = \Cms\Classes\Theme::getActiveTheme();
        $layouts = \Cms\Classes\Layout::listInTheme($theme);
        $layoutNames = $layouts->lists('baseFileName');

        // Check if the page already exists in the active theme
        if (!Page::load($theme, 'unsubscribe.htm')) {
            $page = new Page;

            // Attach the theme explicitly
            // Not needed..!
            // $page->theme = $theme->getDirName();
            // Define page properties
            $page->title = 'Unsubscribe';
            $page->url = '/campaign/unsubscribe/:hash';
            // Will this bring problems? Looks like so..
            // How to get layouts?
            // Pick the first one..
            $page->layout = $layoutNames[0];
            $page->fileName = 'unsubscribe.htm';            
            // This is supposed to work, but doesn't
            /*
            $page->settings['components'] = [
                'unsubscribe' => [
                    'alias' => 'unsubscribe',
                ],
            ];
            */            
            // Works OK
            $page->attributes['unsubscribe'] = [
                'key' => 'value',
            ];
            $page->markup = '{% component "unsubscribe" %}';            

            // Save the page to the theme's pages directory
            $page->save();

            echo "✅ Created unsubscribe page successfully.\n";
        } else {
            echo "ℹ️ Page 'unsubscribe.htm' already exists, skipping.\n";
        }
    }
}
