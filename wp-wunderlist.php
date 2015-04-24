<?php

/*
Plugin Name: WP Wunderlist
Plugin URI: http://set.cooki.me
Description: Wunderlist plugin
Version: 0.0.1
Author: setcooki
Author URI: http://set.cooki.me/me
License: GPLv2 or later
Text Domain: wunderlist
*/

if(!function_exists('add_action') || !defined('ABSPATH'))
{
	echo 'Hi there! I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if(defined('WP_DEBUG') && (int)WP_DEBUG)
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

define('WUNDERLIST_TODO__VERSION', '0.0.1');
define('WUNDERLIST_TODO__MINIMUM_WP_VERSION', '4.0');
define('WUNDERLIST_TODO__API_VERSION', 1);
define('WUNDERLIST_TODO__PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('WUNDERLIST_TODO__PLUGIN_DIR', plugin_dir_path( __FILE__ ));

require_once dirname(__FILE__) . '/boot.php';