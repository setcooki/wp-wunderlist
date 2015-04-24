<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Css;
use Setcooki\Wp\Logger;
use Setcooki\Wp\Option;
use Setcooki\Wp\Controller;
use Setcooki\Wp\Template;

class Front extends Controller
{
    const TEMPLATE_PATH             = 'TEMPLATE_PATH';
    const VIEW_PATH                 = 'VIEW_PATH';
    const PRIMARY_STYLES            = 'PRIMARY_STYLES';
    const THEME_PATH                = 'THEME_PATH';
    const THEME_CUSTOM_PATH         = 'THEME_CUSTOM_PATH';
    const CUSTOM_STYLES             = 'CUSTOM_STYLES';
    const DEFAULT_TYPE              = 'DEFAULT_TYPE';

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
        self::DEFAULT_TYPE          => 'list'
    );


    /**
     * @param Plugin $plugin
     * @param null $options
     */
    public function __construct(Plugin &$plugin, $options = null)
    {
        setcooki_init_options($options, $this);
        if(setcooki_has_option(self::TEMPLATE_PATH, $this, true))
        {
            setcooki_set_option(self::TEMPLATE_PATH, setcooki_path('root') . DIRECTORY_SEPARATOR . trim(setcooki_get_option(self::TEMPLATE_PATH, $this), DIRECTORY_SEPARATOR), $this);
        }else{
            setcooki_set_option(self::TEMPLATE_PATH, setcooki_path('plugin') . '/template/front', $this);
        }
        if(setcooki_has_option(self::VIEW_PATH, $this, true))
        {
            setcooki_set_option(self::VIEW_PATH, setcooki_path('root') . DIRECTORY_SEPARATOR . trim(setcooki_get_option(self::VIEW_PATH, $this), DIRECTORY_SEPARATOR), $this);
        }else{
            setcooki_set_option(self::VIEW_PATH, setcooki_path('plugin') . '/view/front', $this);
        }
        if(!setcooki_has_option(self::PRIMARY_STYLES, $this, true))
        {
            setcooki_set_option(self::PRIMARY_STYLES, setcooki_path('plugin', true) . '/static/css/app.less', $this);
        }
        if(!setcooki_has_option(self::THEME_PATH, $this, true))
        {
            setcooki_set_option(self::THEME_PATH, setcooki_path('plugin', true) . '/static/css/themes', $this);
        }
        if(setcooki_has_option(self::THEME_CUSTOM_PATH, $this, true))
        {
            setcooki_set_option(self::THEME_CUSTOM_PATH, rtrim(setcooki_get_option(self::THEME_CUSTOM_PATH, $this), DIRECTORY_SEPARATOR), $this);
        }
        if(setcooki_has_option(self::CUSTOM_STYLES, $this, true))
        {
            setcooki_set_option(self::CUSTOM_STYLES, rtrim(setcooki_get_option(self::CUSTOM_STYLES, $this), DIRECTORY_SEPARATOR), $this);
        }
        $this->plugin = $plugin;
    }


    /**
     *
     */
    public function init()
    {
        if(!is_admin())
        {
            add_action('wp_enqueue_scripts', array($this, 'enqueue'));
            add_shortcode('wunderlist', array($this, 'render'));
        }

        add_action('wp_ajax_wunderlist_ajax', array($this, 'ajax'));
        add_action('wp_ajax_wunderlist_ajax', array($this, 'ajax'));
        add_action('wp_ajax_nopriv_wunderlist_ajax', array($this, 'ajax'));
        if(!get_option('wunderlist_nonce', false))
        {
            add_option('wunderlist_nonce', wp_create_nonce(substr(substr("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", mt_rand(0 ,50), 1) . substr(md5(time()), 1), 0, 10)));
        }
    }


    /**
     * enqueue scripts and css
     *
     * @return void
     */
    public function enqueue()
    {
        if((int)Option::getByPath('wunderlist_todo_options', 'live.enabled') === 1)
        {
            wp_enqueue_script('socket', setcooki_config('wunderlist', 'socket.client.src'));
        }
        if((int)Option::getByPath('wunderlist_todo_options', 'css.enabled'))
        {
            $style = setcooki_get_option(self::PRIMARY_STYLES, $this);
            $style = apply_filters('wunderlist_todo_style', $style);
            $theme = (string)Option::getByPath('wunderlist_todo_options', 'css.theme', '');
            if(stripos($style, '.less') !== false)
            {
                try
                {
                    $css = setcooki_path('plugin') . '/var/app.min.css';
                    $style = setcooki_path('root') . DIRECTORY_SEPARATOR . ltrim($style, DIRECTORY_SEPARATOR);
                    $theme = setcooki_path('root') . DIRECTORY_SEPARATOR . ltrim($theme, DIRECTORY_SEPARATOR);
                    if(!file_exists($css) || (file_exists($css) && ((int)@filemtime($style) > (int)@filemtime($css)) || ((int)@filemtime($theme) > (int)@filemtime($css))))
                    {
                        require_once setcooki_path('plugin') . '/src/ext/less/Less.php';
                        $parser = new \Less_Parser(array('compress' => true));
                        $parser->SetImportDirs(array(setcooki_path('plugin') . '/static/css/' => setcooki_path('plugin', true) . '/static/css/') );
                        $parser->parse((string)file_get_contents($theme));
                        $parser->parse((string)file_get_contents($style));
                        file_put_contents(setcooki_path('plugin') . '/var/app.min.css', $parser->getCss());
                    }
                    $style = setcooki_path('plugin', true) . '/var/app.min.css';
                }
                catch(\Exception $e)
                {
                    $style = null;
                }
            }
            if(!empty($style))
            {
                wp_enqueue_style('wunderlist', $style, array(), false, 'all');
            }
            if(setcooki_has_option(self::CUSTOM_STYLES, $this))
            {
                wp_enqueue_style('wunderlist-custom', setcooki_get_option(self::CUSTOM_STYLES, $this), array(), false, 'all');
            }
        }
        wp_register_script('wunderlist', setcooki_path('plugin', true) . '/static/js/app.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-sortable'), '1.0.0', false);
        wp_localize_script('wunderlist', 'wunderlistAjax', array('url' => admin_url('admin-ajax.php'), 'nonce' => get_option('wunderlist_nonce')));
        wp_localize_script('wunderlist', 'wunderlistConf', setcooki_config('wunderlist', 'js.conf'));
        wp_enqueue_script('wunderlist');
    }


    /**
     * ajax callback
     */
    public function ajax()
    {
        if($_POST['nonce'] !== Option::get('wunderlist_nonce'))
        {
            //exit("No naughty business please!");
        }

        if(isset($_POST['call']) && !empty($_POST['call']))
        {
            $call = trim((string)$_POST['call']);
            $data = (isset($_POST['data']) && !empty($_POST['data'])) ? $_POST['data'] : array();
            switch($call)
            {
                case 'options'
                :
                    header('Content-Type: application/json');
                    echo (string)file_get_contents(setcooki_path('plugin') . '/var/options.json');
                    exit(0);
                case 'template'
                :
                    /**
                     * TODO: template path/overwrites are done in $this->render() but here i need also the apply filter
                     */
                    if(array_key_exists('template', $data))
                    {
                        $template = $data['template'];
                        $template = setcooki_path('plugin') . '/template/front/default/task.php';
                        if(is_file($template))
                        {
                            echo Template::create($template)->render(null, $data['data']);
                        }
                    }
                    exit(0);
                case 'api'
                :
                    if(array_key_exists('action', $data) && method_exists($this->plugin->api, $data['action']))
                    {
                        $action = $data['action'];
                        $data = (array_key_exists('data', $data)) ? (array)$data['data'] : array();
                        $data = setcooki_typify_array($data);
                        $data = call_user_func_array(array($this->plugin->api, $action), $data);
                        header('Content-Type: application/json');
                        echo json_encode($data);
                        exit(0);
                    }
            }
        }else{
            //do nothing since assuming illegal call
        }
        //delete_option('wunderlist_nonce');
        die();
    }


    /**
     * @param $attr
     * @return string
     */
    public function render($attr)
    {
        try
        {
            if(array_key_exists('id', $attr))
            {
                if(array_key_exists('type', $attr))
                {
                    $attr['type'] = strtolower(trim($attr['type']));
                }else{
                    $attr['type'] = setcooki_get_option(self::DEFAULT_TYPE, $this);
                }
                if(strtolower($attr['type']) === 'list')
                {
                    if((int)Option::getByPath('wunderlist_todo_options', 'live.enabled'))
                    {
                        $this->plugin->webhook->create($attr['id'], 'list');
                    }
                }
                if(array_key_exists('view', $attr))
                {
                    $view = setcooki_path('root') . DIRECTORY_SEPARATOR . ltrim($attr['view'], DIRECTORY_SEPARATOR);
                }else{
                    $view = rtrim(setcooki_get_option(self::VIEW_PATH, $this), ' ' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ucfirst($attr['type']) . 'View.php';
                }
                $view = apply_filters('wunderlist_todo_view', $view);
                if(array_key_exists('template', $attr))
                {
                    $template = setcooki_path('root') . DIRECTORY_SEPARATOR . ltrim($attr['template'], DIRECTORY_SEPARATOR);
                }else{
                    $template = rtrim(setcooki_get_option(self::TEMPLATE_PATH, $this), ' ' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $attr['type'] . '.php';
                }
                $template = apply_filters('wunderlist_todo_template', $template);
                if(is_file($view))
                {
                    if(is_file($template))
                    {
                        require_once $view;
                        $view = basename($view, '.php');
                        if(class_exists($view))
                        {
                            $view = new $view($this);
                            $template = new Template($template, $view);
                            $template->add($attr);
                            return $view->render($template);
                        }else{
                            Logger::l(array("class: %s does not exist", $view), LOG_ERR);
                        }
                    }else{
                        Logger::l(array("template: %s does not exist", $template), LOG_ERR);
                    }
                }else{
                    Logger::l(array("view: %s does not exist", $template), LOG_ERR);
                }
            }else{
                Logger::l("unable to render shortcode due to missing argument: id", LOG_WARNING);
            }
        }
        catch(\Exception $e)
        {
            Logger::l($e);
        }
        return "";
    }
}