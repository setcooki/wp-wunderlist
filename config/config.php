<?php

if(!defined('ABSPATH')) die();

/**
 * NOTE: DON'T CHANGE ANY CONFIG VALUES HERE!
 *
 * this is the default config file for this plugin. dont change any values in this file since they will be overwritten with
 * next plugin update! if you want to overwrite or extend config values you need to create a file named "config.inc" in
 * the root directory of this plugin. to overwrite values use the same syntax and key => value pairs as in this config
 * file as both will be merged together your custom config file overwriting the config values below.
 */

return array
(
    'wp' => array
    (
        'LOGGER' => \Setcooki\Wp\Logger::create(array('LOG_DIR' => dirname(__FILE__) . '/../var/log', 'LOG_LEVEL' => 0))
    ),
    'cache' => array
    (
        'driver' => 'File',
        'options' => array
        (
            'PATH' => dirname(__FILE__) . '/../var/cache',
            'EXPIRATION' => 600
        )
    ),
    'plugin' => array
    (
        'options' => null
    ),
    'api' => array
    (
        'cache' => array
        (
            'lifetime' => 300
        ),
        'options' => array
        (
            'API_URL' => 'https://a.wunderlist.com',
            'API_AUTH_URL' => 'https://www.wunderlist.com/oauth/authorize',
            'API_ACCESS_TOKEN_URL' => 'https://www.wunderlist.com/oauth/access_token'
        )
    ),
    'model' => array
    (
        'map' => array
        (
            'Wunderlist' => '\Setcooki\Wp\Wunderlist\Entity\Wunderlist'
        )
    ),
    'admin' => array
    (
        'options' => array()
    ),
    'front' => array
    (
        'options' => array
        (
            'TEMPLATE_PATH' => null,
            'VIEW_PATH' => null,
            'PRIMARY_STYLES' => null,
            'THEME_PATH' => null,
            'THEME_CUSTOM_PATH' => null,
            'CUSTOM_STYLES' => null,
            'STYLE_INCLUDES' => array
            (
                setcooki_path('plugin', true) . '/static/less/includes/media.less',
                setcooki_path('plugin', true) . '/static/less/includes/responsive.less'
            )
        )
    ),
    'socket' => array
    (
        'client' => array
        (
            'src' => 'https://cdn.socket.io/socket.io-1.3.4.js',
            'default' => array
            (
                'host' => ((isset($_SERVER) && isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : gethostbyname(gethostname())),
                'port' => 7777
            )
        )
    ),
    'js' => array
    (
        'conf' => array
        (
            'dev' => (int)(bool)SETCOOKI_DEV,
            'debug' => (int)SETCOOKI_DEV,
            'ajax' => array
            (
                'url' => admin_url('admin-ajax.php')
            ),
            'deps' => array
            (
                'lib/api.js',
                'lib/action.js',
                'lib/poll.js',
                'lib/socket.js'
            ),
            'css' => array
            (
                'listRoot' => '.wpwl-list',
                'taskRoot' => '.wpwl-task',
                'noteRoot' => '.wpwl-note',
                'tasksRoot' => '.wpwl-tasks'
            )
        )
    )
);