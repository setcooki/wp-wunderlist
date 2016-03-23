<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Logger;
use Setcooki\Wp\Option;
use Setcooki\Wp\Controller;
use Setcooki\Wp\Template;
use Setcooki\Wp\Wunderlist\Controller\Ajax;
use Setcooki\Wp\Wunderlist\Controller\Wunderlist;

/**
 * Class Front
 * @package Setcooki\Wp\Wunderlist
 */
class Front
{
    const TEMPLATE_PATH             = 'TEMPLATE_PATH';
    const VIEW_PATH                 = 'VIEW_PATH';
    const PRIMARY_STYLES            = 'PRIMARY_STYLES';
    const THEME_PATH                = 'THEME_PATH';
    const THEME_CUSTOM_PATH         = 'THEME_CUSTOM_PATH';
    const CUSTOM_STYLES             = 'CUSTOM_STYLES';
    const DEFAULT_VIEW              = 'DEFAULT_VIEW';

    /**
     * @var null|Plugin
     */
    public $plugin = null;

    /**
     * @var null|Template
     */
    public $template = null;

    /**
     * @var array
     */
    public $options = array
    (
        self::DEFAULT_VIEW  => 'List'
    );


    /**
     * @param Plugin $plugin
     * @param null $options
     */
    public function __construct(Plugin &$plugin, $options = null)
    {
        setcooki_init_options($options, $this);
        $this->plugin = $plugin;
        if(setcooki_has_option(self::TEMPLATE_PATH, $this, true))
        {
            setcooki_set_option(self::TEMPLATE_PATH, setcooki_path('root') . DIRECTORY_SEPARATOR . trim(setcooki_get_option(self::TEMPLATE_PATH, $this), DIRECTORY_SEPARATOR), $this);
        }else{
            setcooki_set_option(self::TEMPLATE_PATH, setcooki_path('plugin') . '/templates/front', $this);
        }
        if(setcooki_has_option(self::VIEW_PATH, $this, true))
        {
            setcooki_set_option(self::VIEW_PATH, setcooki_path('root') . DIRECTORY_SEPARATOR . trim(setcooki_get_option(self::VIEW_PATH, $this), DIRECTORY_SEPARATOR), $this);
        }else{
            setcooki_set_option(self::VIEW_PATH, setcooki_path('plugin') . '/views/front', $this);
        }
        if(!setcooki_has_option(self::PRIMARY_STYLES, $this, true))
        {
            setcooki_set_option(self::PRIMARY_STYLES, setcooki_path('plugin', true) . '/static/less/app.less', $this);
        }
        if(!setcooki_has_option(self::THEME_PATH, $this, true))
        {
            setcooki_set_option(self::THEME_PATH, setcooki_path('plugin', true) . '/static/less/themes', $this);
        }
        if(setcooki_has_option(self::THEME_CUSTOM_PATH, $this, true))
        {
            setcooki_set_option(self::THEME_CUSTOM_PATH, rtrim(setcooki_get_option(self::THEME_CUSTOM_PATH, $this), DIRECTORY_SEPARATOR), $this);
        }
        if(setcooki_has_option(self::CUSTOM_STYLES, $this, true))
        {
            setcooki_set_option(self::CUSTOM_STYLES, rtrim(setcooki_get_option(self::CUSTOM_STYLES, $this), DIRECTORY_SEPARATOR), $this);
        }
    }


    /**
     *
     */
    public function init()
    {
        $this->initScripts();

        $this->plugin->resolver
            ->register(new Wunderlist($this->plugin))
            ->register(new Ajax($this->plugin))
            ->bind('shortcode:wunderlist', 'wunderlist::render')
            ->bind('action:wp_ajax_wunderlist_ajax', 'ajax::proxy')
            ->bind('action:wp_ajax_nopriv_wunderlist_ajax', 'ajax::proxy');
    }


    /**
     * enqueue scripts and css
     *
     * @return void
     */
    public function initScripts()
    {
        $self = &$this;

        add_action('wp_enqueue_scripts', function() use (&$self)
        {
            if(Option::get('wp_wunderlist_options.live.mode') === 'push')
            {
                wp_enqueue_script('socket', setcooki_config('socket.client.src'));
            }
            if((int)Option::get('wp_wunderlist_options.css.enabled'))
            {
                $style = setcooki_get_option('PRIMARY_STYLES', $self);
                $style = apply_filters('wp_wunderlist_style', $style);
                $theme = (string)Option::get('wp_wunderlist_options.css.theme', '');
                if(stripos($style, '.less') !== false)
                {
                    try
                    {
                        $css    = setcooki_path('plugin') . '/var/wp-wunderlist.min.css';
                        $style  = setcooki_path('root') . DIRECTORY_SEPARATOR . ltrim($style, DIRECTORY_SEPARATOR);
                        $theme  = setcooki_path('root') . DIRECTORY_SEPARATOR . ltrim($theme, DIRECTORY_SEPARATOR);
                        if(!file_exists($css) || (file_exists($css) && ((int)@filemtime($style) > (int)@filemtime($css)) || ((int)@filemtime($theme) > (int)@filemtime($css))))
                        {
                            $parser = new \Less_Parser(array('compress' => true));
                            $parser->SetImportDirs(array(setcooki_path('plugin') . '/static/less/' => setcooki_path('plugin', true) . '/static/less/') );
                            $parser->parse((string)file_get_contents($theme));
                            $parser->parse((string)file_get_contents($style));
                            file_put_contents(setcooki_path('plugin') . '/var/wp-wunderlist.min.css', $parser->getCss());
                        }
                        $style = setcooki_path('plugin', true) . '/var/wp-wunderlist.min.css';
                    }
                    catch(\Exception $e)
                    {
                        $style = null;
                    }
                }
                if(!empty($style))
                {
                    wp_enqueue_style('wp-wunderlist', $style, array(), false, 'all');
                }
                if(setcooki_has_option('CUSTOM_STYLES', $self))
                {
                    wp_enqueue_style('wunderlist-custom', setcooki_get_option('CUSTOM_STYLES', $self), array(), false, 'all');
                }
            }
            if(SETCOOKI_DEV)
            {
                $js = setcooki_path('plugin', true) . '/static/js/app.js';
            }else{
                $js = setcooki_path('plugin', true) . '/static/js/wp-wunderlist.min.js';
            }
            wp_register_script('wunderlist', $js, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-sortable'), '1.0.0', false);
            wp_localize_script('wunderlist', 'wunderlistAjax', array('url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce(wunderlist_nonce())));
            wp_localize_script('wunderlist', 'wunderlistConf', setcooki_config('js.conf'));
            wp_enqueue_script('wunderlist');
        });
    }
}