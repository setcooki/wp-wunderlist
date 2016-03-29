<?php

namespace Setcooki\Wp\Wunderlist\Entity;

/**
 * Class Wunderlist
 * @package Setcooki\Wp\Wunderlist\Entity
 */
class Wunderlist extends Entity
{
	public $id = null;
	public $created_at = null;
	public $title = null;
	public $list_type  = null;
	public $type = null;
	public $revision = null;
	public $tasks = null;
	public $position_id = null;
	public $position_revision = null;

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return wunderlist_linkify($this->title, '_blank');
	}
}