<?php

namespace Setcooki\Wp\Wunderlist\Model;

use Setcooki\Wp\Wunderlist\Entity\Note;
use Setcooki\Wp\Wunderlist\Entity\Task;

/**
 * Class Wunderlist
 * @package Setcooki\Wp\Wunderlist\Model
 */
class Wunderlist extends Model
{
	/**
	 * @param $id
	 *
	 * @return \Setcooki\Wp\Wunderlist\Entity\Wunderlist
	 */
	public function get($id)
	{
		$tmp = array();

		$list       = $this->api()->getList($id);
		$tasks      = $this->api()->getTasks($id);
		$notes      = $this->api()->getNotes($id, 'list');
		$positions  = $this->api()->getPositions($id);

		$list = new $this->entity($list);

		foreach($notes as $val)
		{
			$tmp[(int)$val->task_id] = new Note($val);
		}
		$notes = $tmp;
		$tmp = array();
		foreach($tasks as &$val)
		{
			$tmp[(int)$val->id] = new Task($val);
			if(array_key_exists($val->id, $notes))
			{
				$tmp[(int)$val->id]->has_note = true;
				$tmp[(int)$val->id]->note = $notes[$val->id];
			}
		}
		$tasks = $tmp;
		$tmp = array();
		if(is_array($positions) && sizeof($positions) >= 1)
	    {
	        $list->position_id = (int)$positions[0]->id;
	        $list->position_revision = (int)$positions[0]->revision;
	        foreach($positions[0]->values as $val)
	        {
	            if(array_key_exists((int)$val, $tasks))
	            {
					$tmp[] = $tasks[(int)$val];
	            }
	        }
		    $tasks = $tmp;
	    }
		$list->tasks = $tasks;

		unset($positions);
		unset($tasks);
		unset($notes);
		unset($tmp);

		return $list;
	}
}