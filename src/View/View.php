<?php

namespace Setcooki\Wp\Wunderlist\View;

use Setcooki\Wp\Option;
use Setcooki\Wp\Template;
use Setcooki\Wp\Util\Params;

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
	 * @param $params
	 * @return mixed
	 * @throws \Setcooki\Wp\Exception
	 */
	public function execute($params)
	{
		$this->params = $params = new Params($params);

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
	}


	/**
	 * @param Params $params
	 *
	 * @return mixed
	 */
	abstract public function compile(Params $params);
}