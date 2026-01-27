<?php namespace Pbs\Campaign\Updates;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Winter\Storm\Database\Updates\Seeder;

class SeedSubscribePage extends Seeder
{
    public function run()
    {        
        $theme = \Cms\Classes\Theme::getActiveTheme();
        $layouts = \Cms\Classes\Layout::listInTheme($theme);
        $layoutNames = $layouts->lists('baseFileName');

        // Check if the page already exists in the active theme
        if (!Page::load($theme, 'confirm.htm')) {
            $page = new Page;

            // Define page properties
            $page->title = 'Confirm';
            $page->url = '/campaign/confirm/:hash';
            // Will this bring problems? Looks like so..
            // How to get layouts?
            // Pick the first one..
            $page->layout = $layoutNames[0];
            $page->fileName = 'confirm.htm';            
            // Works OK
            $page->attributes['confirm'] = [
                'key' => 'value',
            ];
            $page->markup = '{% component "confirm" %}';            

            // Save the page to the theme's pages directory
            $page->save();

            echo "✅ Created confirm page successfully.\n";
        } else {
            echo "ℹ️ Page 'confirm.htm' already exists, skipping.\n";
        }
    }
}
