<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Template;
use Setcooki\Wp\View;
use Setcooki\Wp\Option;

class ListView extends View
{
    /**
     * @param Template $template
     * @return void
     */
    protected function renderTasks(Template $template)
    {
        $tmp = array();

        if($template->has('id'))
        {
            $id = (int)$template->get('id');

            if(($cache = setcooki_cache("list:$id")) === false)
            {
                $api = $this->controller->plugin->api;
                $list = $api->getList($id);
                $tasks = $api->getTasks($id);
                $notes = $api->getNotes($id, 'list');
                $positions = $api->getPositions($id);
                $options = Option::get('wunderlist_todo_options', array());

                $list->label = htmlentities($list->title);
                if(array_key_exists('list', $options) && isset($options['list']['title_wrapper']) && !empty($options['list']['title_wrapper']))
                {
                    $wrapper = trim($options['list']['title_wrapper'], ' <>');
                    if(preg_match('=^([^\s]+)(.*)$=i', $wrapper, $m))
                    {
                        $list->title = vsprintf('<%s>%s</%s>', array($m[0], $list->title, $m[1]));
                    }
                }

                foreach($tasks as $k => $v)
                {
                    $v->label = htmlentities(trim($v->title));
                    $v->title = setcooki_linkify($v->title, '_blank');
                    $tasks[$v->id] = $v; unset($tasks[$k]);
                }
                foreach($notes as $k => $v)
                {
                    $v->content = setcooki_linkify($v->content, '_blank');
                    $notes[$v->task_id] = $v; unset($notes[$k]);
                }

                //compile tasks by position
                if(is_array($positions) && sizeof($positions) >= 1)
                {
                    $list->position_id = (int)$positions[0]->id;
                    $list->position_revision = (int)$positions[0]->revision;
                    foreach($positions[0]->values as $p)
                    {
                        $p = (int)$p;
                        if(array_key_exists($p, $tasks))
                        {
                            $task =& $tasks[$p];
                            if(array_key_exists((int)$task->id, $notes) && !empty($notes[(int)$task->id]->content))
                            {
                                $task->has_note = true;
                                $task->note = $notes[(int)$task->id];
                            }else{
                                $task->has_note = false;
                                $task->note = null;
                            }
                            $tmp[] = $task;
                        }
                    }
                    $tasks = $tmp;
                }
                setcooki_cache("list:$id", array($list, $tasks), (int)setcooki_config('wunderlist', 'api.cache.lifetime'));
                $template->add('list', $list);
                $template->add('tasks', $tasks);
            }else{
                $template->add('list', $cache[0]);
                $template->add('tasks', $cache[1]);
            }
        }else{
            $template->add('list', null);
            $template->add('tasks', null);
        }
    }


    /**
     * @param Template $template
     * @return mixed|string
     * @throws \Setcooki\Wp\Exception
     */
    public function render(Template $template)
    {
        if($template->has('id'))
        {
            $this->renderTasks($template);
            $template->add('options', Option::get('wunderlist_todo_options', array()));
            return $template->render();
        }
        return "";
    }
}