<?php

namespace Setcooki\Wp\Wunderlist\Wunderlist\File;

use Setcooki\Wp\Util\Params;

/**
 * Class File
 * @package Setcooki\Wp\Wunderlist\Wunderlist\File
 */
abstract class File
{
	/**
	 * @var null|Params
	 */
	public $params = null;


	/**
	 * File constructor
	 *
	 * @param $params
	 */
	public function __construct($params)
	{
		$this->params = new Params($params);
	}


	/**
	 * @return mixed
	 */
	abstract public function render();
}