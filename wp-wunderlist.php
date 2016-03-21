<?php

/*
Plugin Name: WP Wunderlist
Plugin URI: http://set.cooki.me
Description: Wunderlist Plugin (Basic). Present your Wunderlist lists on your wordpress blog
Version: 0.0.1
Author: setcooki
Author URI: http://set.cooki.me/me
License: GPLv3 or later
Text Domain: wunderlist
*/

if(!function_exists('add_action') || !defined('ABSPATH'))
{
	echo 'Hi there! I\'m just a plugin, not much I can do when called directly.';
	exit;
}

//WP_DEBUG = enable/disable debug
//WP_DEBUG_DISPLAY = enable/disable displaying of debug messages (is enabled always
//WP_DEBUG_LOG = enable/disable logging of debug message

/**
 * if WP_DEBUG = true
 * - log all events to screen!
 *
 * if WP_DEBUG = true and logger is set
 * - log all event to file
 */

define('WP_WUNDERLIST__VERSION', '0.0.1');
define('WP_WUNDERLIST__MINIMUM_WP_VERSION', '4.0');
define('WP_WUNDERLIST__PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('WP_WUNDERLIST__PLUGIN_DIR', plugin_dir_path( __FILE__ ));

require_once dirname(__FILE__) . '/boot.php';