<?php

namespace Setcooki\Wp\Wunderlist\Model;

use Setcooki\Wp\Wunderlist\Api;
use Setcooki\Wp\Wunderlist\Entity\Entity;
use Setcooki\Wp\Wunderlist\Exception;

/**
 * Class Model
 * @package Setcooki\Wp\Wunderlist\Model
 */
abstract class Model
{
	/**
	 * @var null
	 */
	public $entity = null;

	/**
	 * @var null
	 */
	protected static $_api = null;

	/**
	 * @var array
	 */
	protected static $_map = array();

	/**
	 * @var array
	 */
	protected static $_cache = array();


	/**
	 * @param $api
	 */
	public static function setApi($api)
	{
		self::$_api = $api;
	}

	/**
	 * @return null
	 */
	public static function getApi()
	{
		return self::$_api;
	}


	/**
	 * @param $map
	 */
	public static function setModel($map)
	{
		self::$_map = $map;
	}


	/**
	 * @param $caller
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function getModel($caller)
	{
		if(stripos($caller, NAMESPACE_SEPARATOR) !== false)
		{
			$caller = substr($caller, strripos($caller, NAMESPACE_SEPARATOR) + 1);
		}else{
			$caller = (string)$caller;
		}
		$model = __NAMESPACE__ . NAMESPACE_SEPARATOR . $caller;
		if(array_key_exists($caller, self::$_map))
		{
			$entity = self::$_map[$caller];
		}else{
			$entity =  '\Setcooki\Wp\Wunderlist\Entity\\' . $caller;
		}
		if(class_exists($model) && class_exists($entity))
		{
			if(!array_key_exists($model, self::$_cache))
			{
				self::$_cache[$model] = new $model();
				self::$_cache[$model]->entity = $entity;
			}
			return self::$_cache[$model];
		}else{
			throw new Exception("");
		}
	}


	/**
	 * @param Entity $entity
	 */
	public function setEntity(Entity $entity)
	{
		$this->entity = $entity;
	}


	/**
	 * @return null
	 */
	public function getEntity()
	{
		return $this->entity;
	}


	/**
	 * @return null
	 */
	protected function api()
	{
		return self::getApi();
	}


	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	abstract public function get($id);
}