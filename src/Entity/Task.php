<?php

namespace Setcooki\Wp\Wunderlist\Entity;

/**
 * Class Task
 * @package Setcooki\Wp\Wunderlist\Entity
 */
class Task extends Entity
{
	public $id = null;
	public $created_at = null;
	public $due_date = null;
	public $list_id = null;
	public $revision = null;
	public $starred = null;
	public $title = null;
	public $note = null;
	public $has_note = false;
}