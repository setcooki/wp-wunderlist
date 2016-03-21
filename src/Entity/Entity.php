<?php

namespace Setcooki\Wp\Wunderlist\Entity;

use Setcooki\Wp\Traits\Setget;
use Setcooki\Wp\Wp;

/**
 * Class Entity
 * @package Setcooki\Wp\Wunderlist\Entity
 */
abstract class Entity
{
	use Setget;


	/**
	 * Base constructor.
	 *
	 * @param null $data
	 */
	public function __construct($data = null)
	{
		$this->set($data);
	}
}