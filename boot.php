<?php

$logger = null;

try
{
    define('SETCOOKI_WP_AUTOLOAD', 0);

    require_once dirname(__FILE__) . '/lib/vendor/autoload.php';
    require_once dirname(__FILE__) . '/lib/vendor/setcooki/wp/core.php';

    $conf = array
    (
        SETCOOKI_WP_CONFIG => array(array
        (
            dirname(__FILE__) . '/config/config.inc',
            dirname(__FILE__) . '/config.inc'
        ), 'wunderlist')
    );
    setcooki_init($conf);

    $logger = Setcooki\Wp\Logger::create
    (
        setcooki_config('wunderlist', 'logger.dir'),
        setcooki_config('wunderlist', 'logger.options')
    );

    $cache = Setcooki\Wp\Cache::factory
    (
        setcooki_config('wunderlist', 'cache.driver'),
        setcooki_config('wunderlist', 'cache.options'),
        'c0'
    );

    if(function_exists('add_action'))
    {
        add_action('init', array(new Setcooki\Wp\Wunderlist\Plugin(setcooki_config('wunderlist', 'plugin.options')), 'init'));
    }
}
catch(Exception $e)
{
    echo ($logger) ? $logger->log($e) : $e->getMessage();
}