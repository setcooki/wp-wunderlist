<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Controller;
use Setcooki\Wp\Option;

class Admin extends Controller
{
    /**
     * @var null|Plugin
     */
    public $plugin = null;

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
        $self = &$this;

        //admin init
        add_action('admin_init', function() use (&$self)
        {
            $todo_options = function($options)
            {
                if(!is_array($options) || empty($options) || ($options === false))
                {
                    return array();
                }else{
                    return $options;
                }
            };
            $plugin_action_links = function($links)
            {
                array_unshift($links, '<a href="options-general.php?page=wunderlist_todo_settings">Settings</a>');
                return $links;
            };
            register_setting('wunderlist-todo-setting-group', 'wunderlist_todo_client_id');
            register_setting('wunderlist-todo-setting-group', 'wunderlist_todo_client_secret');
            register_setting('wunderlist-todo-setting-group', 'wunderlist_todo_options', $todo_options);
            add_filter("plugin_action_links", $plugin_action_links);
        });

        //admin menu init
        add_action('admin_menu', function() use (&$self)
        {
            $options_page = function() use (&$self)
            {
                if(!current_user_can('manage_options'))
                {
                    wp_die(__('You do not have sufficient permissions to access this page.'));
                }
                require_once setcooki_path('plugin') . '/template/admin/settings.php';
            };
            add_options_page('Wunderlist Todo Settings', 'Wunderlist Todo', 'manage_options', 'wunderlist_todo_settings', $options_page);
        });

        add_action('admin_enqueue_scripts', function($hook)
        {
            if($hook !== 'settings_page_wunderlist_todo_settings')
            {
                return;
            }
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-form');
            wp_enqueue_script('wunderlist-settings', setcooki_path('plugin', true) . '/template/admin/settings.js');
        });

        //listen for admin actions
        if(isset($_SERVER['REQUEST_METHOD']) && strtolower((string)$_SERVER['REQUEST_METHOD']) === 'get' && isset($_GET['page']) && $_GET['page'] === 'wunderlist_todo_settings' && isset($_GET['action']))
        {
            $this->action($_GET['action']);
        }

        //save options
        if
        (
            (array_key_exists('page', $_GET) && $_GET['page'] == 'wunderlist_todo_settings')
            &&
            (array_key_exists('settings-updated', $_GET) && $_GET['settings-updated'] == 'true'))
        {
            $this->save();
        }
    }


    /**
     * @param $action
     */
    protected function action($action)
    {
        if(!empty($action))
        {
            $action = strtolower(trim($action));
            switch($action)
            {
                case 'oauth-api':
                    wp_redirect($this->plugin->api->getAuthLink(get_option('wunderlist_todo_auth_state', '')));
                    exit();
                    break;
                case 'webhook-reset':
                    $this->plugin->webhook->deleteAll();
                    break;
                case 'cache-flush':
                    setcooki_cache();
                    break;
                default:
                    //do nothing
            }
            header('Location: ' . admin_url('options-general.php?page=wunderlist_todo_settings'));
        }
    }


    /**
     * save options in cache and remove cached vars
     *
     * @return void
     */
    protected function save()
    {
        $css = setcooki_path('plugin') . '/var/app.min.css';
        $json = setcooki_path('plugin'). '/var/options.json';
        $options = Option::get('wunderlist_todo_options', array());

        if(is_file($css))
        {
            @unlink($css);
        }
        if(is_file($json))
        {
            @chmod($json, 0755);
        }
        @file_put_contents($json, json_encode($options));
    }
}