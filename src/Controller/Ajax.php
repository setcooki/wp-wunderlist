<?php

namespace Setcooki\Wp\Wunderlist\Controller;

use Setcooki\Wp\Controller\Controller;
use Setcooki\Wp\Option;
use Setcooki\Wp\Request;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Wunderlist\Exception;

/**
 * Class Ajax
 * @package Setcooki\Wp\Wunderlist\Controller\Ajax
 */
class Ajax extends Controller
{
	/**
	 * @param Params $params
	 * @param Request $request
	 */
	public function proxy(Params $params, Request $request)
	{
		try
		{
			$params = new Params($request->getParams());
			if(!$params->is('nonce') || !wp_verify_nonce($params->get('nonce'), 'wp-wunderlist'))
			{
				throw new Exception('missing or non matching ajax nonce');
			}
			if($params->is('call'))
	        {
		        $call = $params->get('call');
		        $data = $params->get('data', array());
		        $method = new \ReflectionMethod($this, $call);
		        if(!$method->isPublic())
		        {
			        throw new Exception('trying to call action which is not public');
		        }
		        if($method->isStatic())
		        {
			        throw new Exception('trying to call action which is static');
		        }
		        echo $this->$call(new Params($data));
		        exit(0);
	        }else{
				throw new Exception('ajax request with missing or empty call parameter');
			}
		}
		catch(\Exception $e)
		{
			print_r($e);
			exit(0);
		}
	}


	/**
	 * @param Params $params
	 * @return void
	 */
	public function options(Params $params)
	{
		header('Content-Type: application/json');
        echo (string)file_get_contents(setcooki_path('plugin') . '/var/options.json');
        exit(0);
	}


	/**
	 * @param Params $params
	 * @return string
	 */
	public function render(Params $params)
	{
		if($params->is('type') && $params->is('id'))
		{
			$params->set('cache', false);
			return setcooki_handle('wunderlist::render', $params);
		}
	}
}

/*
  case 'template'
 :

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
 */