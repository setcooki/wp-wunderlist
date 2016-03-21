<?php

try
{
    if(isset($_GET['__dev']) && (int)$_GET['__dev'] >= 1)
    {
    	define('SETCOOKI_DEV', (int)$_GET['__dev']);
    }else{
        define('SETCOOKI_DEV', false);
    }
    define('SETCOOKI_WP_AUTOLOAD', 0);
    require_once dirname(__FILE__) . '/lib/vendor/autoload.php';
    require_once dirname(__FILE__) . '/lib/vendor/setcooki/wp/core.php';
    require_once dirname(__FILE__) . '/inc/helper.php';

    if((bool)setcooki_option('wp_wunderlist_options.admin.debug'))
    {
        setcooki_conf(SETCOOKI_WP_DEBUG, true);
    }
    if((bool)setcooki_option('wp_wunderlist_options.admin.log'))
    {
        setcooki_conf(SETCOOKI_WP_LOG, true);
    }
    $options = array
    (
        dirname(__FILE__) . '/config/config.php',
        dirname(__FILE__) . '/config.php'
    );
    if(SETCOOKI_DEV && is_file(dirname(__FILE__) . '/config/config-dev.php'))
    {
        array_push($options, dirname(__FILE__) . '/config/config-dev.php');
    }
    setcooki_boot($options);
    if(isset($_GET['__cache']) && (int)$_GET['__cache'] === -1)
    {
        //do not use cache
    }else{
        $cache = \Setcooki\Wp\Cache\Cache::factory
        (
            setcooki_config('cache.driver'),
            setcooki_config('cache.options'),
            'c0'
        );
    }
    if(function_exists('add_action'))
    {
        add_action('init', array(Setcooki\Wp\Wunderlist\Plugin::instance(get_current_blog_id(), setcooki_config('plugin.options')), 'init'));
    }
}
catch(Exception $e)
{
    ((function_exists('setcooki_log')) ? setcooki_log($e) : (((defined('WP_DEBUG') && (bool)WP_DEBUG) || SETCOOKI_WP_DEBUG) ? print($e->getMessage()) : ''));
}