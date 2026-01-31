<?php

namespace Pbs\Campaign;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;
use Backend\Classes\Controller;
use Event;
use Route;
use Log;
use System\Classes\MailManager;
// use System\Classes\CombineAssets;
use System\Models\MailBrandSetting;


/**
 * Campaign Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'pbs.campaign::lang.plugin.name',
            'description' => 'pbs.campaign::lang.plugin.description',
            'author'      => 'Pbs',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        $this->registerConsoleCommand('campaign:seed', 'Pbs\Campaign\Console\SeedCampaignData');
        $this->registerConsoleCommand('campaign:settings', 'Pbs\Campaign\Console\SeedSettings');        
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        // Generic exception handler
        \App::error(function (\Exception $exception) {
            // Check if the exception is a throttle exception and log it
            if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                Log::info('Throttle error: ' . $exception->getMessage());
            }
        });

        // Throttle the unsubscribe route
        \Route::middleware(['web', 'throttle:5,1'])
        ->match(['get', 'post'], '/campaign/unsubscribe/{hash}', function($hash) {
            $controller = new \Cms\Classes\Controller();
            // Render the unsubscribe page
            // Needs the URL, and not the filename!
            // Some things are not on the docs, and AI just talks gibberish 
            // So the best is to check the source code..!
            return $controller->run("/campaign/unsubscribe/$hash");
        });

        // Throttle the confirm route
        \Route::middleware(['web', 'throttle:5,1'])
        ->match(['get', 'post'], '/campaign/confirm/{hash}', function($hash) {
            $controller = new \Cms\Classes\Controller();
            return $controller->run("/campaign/confirm/$hash");
        });

        // Register event subscribers
        Event::subscribe(\Pbs\Campaign\Classes\EventHandlers::class);
    }    

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Campaign Settings',
                'description' => 'Manage campaign settings',
                'category'    => 'Campaign',
                'icon'        => 'icon-cog',
                'class'       => \Pbs\Campaign\Models\Settings::class,
                'order'       => 501,
                'keywords'    => 'campaign settings',
                'permissions' => ['pbs.campaign.manage_settings']
            ],
        ];
    }


    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            // this will display the newsletter on the frontend - still in tests / WIP
            'Pbs\Campaign\Components\Newsletter' => 'newsletter',
            'Pbs\Campaign\Components\Unsubscribe' => 'unsubscribe',
            'Pbs\Campaign\Components\Subscribe' => 'subscribe',
            'Pbs\Campaign\Components\Confirm' => 'confirm',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return [
            'pbs.campaign.manage' => [
                'tab' => 'pbs.campaign::lang.plugin.name',
                'label' => 'pbs.campaign::lang.permissions.manage',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return [
            'campaign' => [
                'label'       => 'pbs.campaign::lang.plugin.name',
                'url'         => Backend::url('pbs/campaign/newsletters'),
                'icon'        => 'icon-leaf',
                'permissions' => ['pbs.campaign.manage'],
                'order'       => 500,
                'sideMenu'    => [
                    'newsletters' => [
                        'label' => 'Newsletters',
                        'icon'  => 'icon-envelope',
                        'url'   => Backend::url('pbs/campaign/newsletters'),
                    ],
                    'subscribers' => [
                        'label' => 'Subscribers',
                        'icon'  => 'icon-user',
                        'url'   => Backend::url('pbs/campaign/subscribers'),
                    ],
                    'lists' => [
                        'label' => 'Lists',
                        'icon'  => 'icon-list',
                        'url'   => Backend::url('pbs/campaign/lists'),
                    ],
                    'campaigns' => [
                        'label' => 'Campaigns',
                        'url' => Backend::url('pbs/campaign/campaigns'),
                        'icon' => 'icon-paper-plane',                        
                        'order' => 510,                        
                    ],
                    'clicks' => [
                        'label' => 'Clicks',
                        'url' => Backend::url('pbs/campaign/clicks'),
                        'icon' => 'icon-mouse-pointer',                        
                        'order' => 520,                        
                    ],
                    'visualizations ' => [
                        'label' => 'Visualizations',
                        'url' => Backend::url('pbs/campaign/visualizations'),
                        'icon' => 'icon-eye',
                        'order' => 530,
                    ],
                    'leads' => [
                        'label' => 'Leads',
                        'url' => Backend::url('pbs/campaign/leads'),
                        'icon' => 'icon-star',
                        'order' => 540,
                    ],
                ],
            ],
        ];
    }

    // not used?
    public function registerMailTemplates(): array
    {
        // if a "migration" already does this, guess this is redundant?
        // it seems that for templates register is needed
        // but for layouts it's not..
        return [
            'pbs.campaign::newsletter.index',
            'pbs.campaign::newsletter.confirmation',
        ];
    }
    
    // templates use layouts
    // is the register needed? yes.
    // using the migration for now
    public function registerMailLayouts()
    {
        return [
            // 'newsletter' => 'pbs.campaign::newsletter.layout-email',
        ];
    }
    
    /*
    // not used, but just to know it's possible
    public function registerMailPartials()
    {
        return [
            'text_block'  => 'pbs.campaign::newsletter.partials.text_block',
            'image_block' => 'pbs.campaign::newsletter.partials.image_block',
        ];
    }
    */

    public function registerFormWidgets(): array
    {
        return [
            // 'Pbs\Campaign\FormWidgets\RichEditor\InsertName' => 'insertName',
            // 'Pbs\Campaign\FormWidgets\RichEditor\Emoji' => 'emoji',
        ];
    }

    public function registerMarkupTags(): array
    {
        return [
            'filters' => [
                // 'test' => [$this, 'test'],
                // this parses content from DB that may contain Twig
                'parse' => function($content, $data = []) {
                    return \Twig::parse($content, $data);
                },
                'html_entity_decode' => function($content) {
                    return html_entity_decode($content);
                },
                'html_to_text' => function($content) {
                    // Replace &nbsp; with a standard space
                    $content = str_replace('&nbsp;', ' ', $content);
                    // Replace <br> with newlines
                    $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
                    // Replace closing block tags with double newlines
                    $content = preg_replace('/<\/(p|h[1-6]|div|blockquote)>/i', "\n\n", $content);
                    // Strip remaining tags
                    return strip_tags($content);
                }
            ],
            // playing with mail brand settings..
            /*
            'functions' => [
                'get_brand_setting' => function($key, $default = null) {
                    return \System\Models\MailBrandSetting::get($key, $default);
                } 
            ]
            */
        ];
    }

    // clean up?
    /*
    public function test($value, $hash)
    {
        if ($hash) {
            return str_replace('{recipient_hash}', $hash, $value);
        }

        return $value;
    }
    */

    /*
    public function registerMailDrivers()
    {
        return [
            'preview' => [
                'class' => \Pbs\Campaign\Drivers\PreviewMailDriver::class,
                'name' => 'Preview',
                'description' => 'Preview emails in the browser instead of sending them',
            ]
        ];
    }
    */
}