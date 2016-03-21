<?php

namespace Setcooki\Wp\Wunderlist\Controller\Admin;

use Setcooki\Wp\Request;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Wp;
use Setcooki\Wp\Option;

/**
 * Class Settings
 * @package Setcooki\Wp\Wunderlist\Controller
 */
class Settings extends Admin
{
	/**
	 * Settings constructor.
	 *
	 * @param Wp $wp
	 * @param null $options
	 */
	public function __construct(Wp $wp, $options = null)
	{
		parent::__construct($wp, $options);
	}


	/**
	 *
	 */
	public function init()
	{
		register_setting('wp-wunderlist-setting-group', 'wp_wunderlist_client_id');
		register_setting('wp-wunderlist-setting-group', 'wp_wunderlist_client_secret');
		register_setting('wp-wunderlist-setting-group', 'wp_wunderlist_options', array($this, 'validate'));
		add_filter('pre_update_option_wp_wunderlist_options', array($this, 'update'));
	}


	/**
	 *
	 */
	public function display()
	{
		if(!current_user_can('manage_options'))
        {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        require_once setcooki_path('plugin') . '/templates/admin/settings.php';
		die();
	}


	/**
	 * @param Params|null $params
	 * @param Request $request
	 */
	public function action(Params $params = null, Request $request)
	{
		$action = $request->get('action');
		if(!empty($action))
        {
	        $action = strtolower(trim($action));
	        switch($action)
	        {
		        case 'oauth-api':
			        Request::redirect($this->wp->api->getAuthLink(get_option('wp_wunderlist_auth_state', '')));
		            break;
		        case 'webhook-reset':
		            $this->wp->webhook->deleteAll();
			        break;
		        case 'cache-flush':
		            setcooki_cache();
		            break;
		        case 'empty-logs':
					if(($logger = setcooki_conf('LOGGER')) !== null)
			        {
				        $logger->clear();
			        }
			        break;
		        default:
			        //do nothing
	        }
	        Request::redirect(admin_url('options-general.php?page=wp_wunderlist_settings'));
        }
	}


	/**
	 * @param $options
	 *
	 * @return array
	 */
	public function validate($options)
	{
		if(!is_array($options) || empty($options) || ($options === false))
	    {
		    $options = array();
	    }
		if(isset($options['security']['whitelist']) && empty($options['security']['whitelist']))
		{
			add_settings_error('wp_wunderlist_options', esc_attr('wp_wunderlist_security_whitelist'), __('Security > Whitelist ID´s can not be empty'), 'error');
		}
		return $options;
	}


	/**
	 * @param $options
	 *
	 * @return array
	 */
	public function update($options)
	{
		if(!is_array($options) || empty($options) || ($options === false))
	    {
		    $options = array();
	    }
		if(isset($options['security']['whitelist']) && !empty($options['security']['whitelist']))
		{
			$options['security']['whitelist'] = preg_replace('=\s*(,|;)\s*=i', "\r\n", trim($options['security']['whitelist']));
		}
		return $options;
	}


	/**
	 *
	 */
	public function save()
	{
		$css = setcooki_path('plugin') . '/static/css/wp-wunderlist.min.css';
	    $json = setcooki_path('plugin'). '/var/options.json';
	    $options = Option::get('wp_wunderlist_options', array());

	    if(is_file($css))
	    {
	        @unlink($css);
	    }
	    if(is_file($json))
	    {
	        @chmod($json, 0755);
	    }
	    @file_put_contents($json, json_encode($options));
		return true;
	}
}