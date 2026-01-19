<?php namespace Pbs\Campaign\Updates;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Winter\Storm\Database\Updates\Seeder;

class SeedNewsletterPage extends Seeder
{
    public function run()
    {
        $theme = \Cms\Classes\Theme::getActiveTheme();
        $layouts = \Cms\Classes\Layout::listInTheme($theme);
        $layoutNames = $layouts->lists('baseFileName');

        // Check if the page already exists in the active theme
        if (!Page::load($theme, 'newsletter.htm')) {
            $page = new Page;

            $page->title = 'Newsletter';
            $page->url = '/campaign/newsletter/:id/:hash?';
            $page->layout = $layoutNames[0] ?? 'default';
            $page->fileName = 'newsletter.htm';            
            
            $page->attributes['newsletter'] = [
                'key' => 'value',
            ];
            $page->markup = '{% component "newsletter" %}';

            // Save the page to the theme's pages directory
            $page->save();

            echo "✅ Created newsletter page successfully.\n";
        } else {
            echo "ℹ️ Page 'newsletter.htm' already exists, skipping.\n";
        }
    }
}
