<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Controller\Resolver;
use Setcooki\Wp\Option;
use Setcooki\Wp\Routing\Route;
use Setcooki\Wp\Wunderlist\Controller\Admin\Settings;

/**
 * Class Admin
 * @package Setcooki\Wp\Wunderlist
 */
class Admin
{
    /**
     * @var null|Plugin
     */
    public $plugin = null;

    /**
     * @var null
     */
    public $settings = null;

    /**
     * @var array
     */
    public $options = array();


    /**
     * @param Plugin $plugin
     * @param null $options
     */
    public function __construct(Plugin &$plugin, $options = null)
    {
        setcooki_init_options($options, $this);
        $this->plugin = $plugin;
    }


    /**
     *
     */
    public function init()
    {
        if(is_admin())
        {
            $this->settings = new Settings($this->plugin);

            $this->initScripts();
            $this->initMenus();

            $this->plugin->resolver
                ->register($this->settings)
                ->bind('action:admin_init', 'settings::init');
            $this->plugin->router->add(array
            (
                new Route('url:%page=wp_wunderlist_settings&settings-updated=true', 'Settings::save'),
                new Route('url:%page=wp_wunderlist_settings&action=webhook-reset', 'Settings::action'),
                new Route('url:%page=wp_wunderlist_settings&action=cache-flush', 'Settings::action'),
                new Route('url:%page=wp_wunderlist_settings&action=empty-logs', 'Settings::action'),
                new Route('url:%page=wp_wunderlist_settings&action=oauth-api', 'Settings::action')
            ));
            $this->plugin->resolver->handle($this->plugin->router);
        }
    }


    /**
     *
     */
    public function initMenus()
    {
        $self = &$this;

        add_action('admin_init', function() use (&$self)
        {
            $links = function($links)
            {
                array_unshift($links, '<a href="options-general.php?page=wp_wunderlist_settings">Settings</a>');
                return $links;
            };
            add_filter("plugin_action_links", $links);
        });
        add_action('admin_menu', function() use (&$self)
        {
            add_options_page('WP Wunderlist Settings', 'WP Wunderlist', 'manage_options', 'wp_wunderlist_settings', array($self->settings, 'display'));
        });
    }


    /**
     *
     */
    public function initScripts()
    {
        add_action('admin_enqueue_scripts', function($hook)
        {
            if($hook !== 'settings_page_wp_wunderlist_settings')
            {
                return;
            }
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-form');
            wp_enqueue_script('wp-wunderlist-settings', setcooki_path('plugin', true) . '/templates/admin/settings.js');
        });
    }
}