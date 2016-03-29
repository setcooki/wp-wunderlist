<?php

namespace Setcooki\Wp\Wunderlist\View;

use Setcooki\Wp\Option;
use Setcooki\Wp\Template;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Wunderlist\Model\Model;

/**
 * Class Base
 * @package Setcooki\Wp\Wunderlist\View
 */
abstract class View extends \Setcooki\Wp\Controller\View\View
{
	/**
	 * @var null
	 */
	public $params = null;


	/**
	 * @param Params $params
	 *
	 * @return mixed
	 */
	abstract public function compile(Params $params);


	/**
	 * @param $params
	 * @return mixed
	 * @throws \Setcooki\Wp\Exception
	 */
	public function execute($params)
	{
		$list = null;

		$this->params = $params = new Params($params);

		if($params->has('id'))
        {
	        $id = (int)$params->get('id');
	      	if($params->get('cache'))
	      	{
		        if(($cache = setcooki_cache("list:$id")) === false)
		        {
			        $list = Model::getModel('Wunderlist')->get($id);
			        setcooki_cache("list:$id", $list, (($params->is('cache')) ? (int)$params->get('cache') : (int)setcooki_config('api.cache.lifetime')));
		        }else{
			        $list = $cache;
		        }
	        }else{
		        $list = Model::getModel('Wunderlist')->get($id);
	      	}
	        if(sizeof($list->tasks) < Option::get('wp_wunderlist_options.list.task_limit', 1000))
	        {
				return false;
	        }
			$this->assign('list', $list);
	        $this->assign('options', Option::get('wp_wunderlist_options', array()));
	        if($params->has('cache') && (bool)$params->get('cache') === false)
            {
                $params->set('cache',  false);
            }else if($params->has('cache') && is_numeric($params->get('cache')) && (int)$params->get('cache') >= 1){
                $params->set('cache',  (int)$params->get('cache'));
            }else{
                $params->set('cache',  true);
            }
            if($params->is('class'))
            {
                $this->assign('class', setcooki_sprintf('wpwl %s', trim($params->get('class'))));
            }else{
                $this->assign('class', 'wpwl');
            }
            $this->compile($params);
        }else{
			return false;
		}
	}
}