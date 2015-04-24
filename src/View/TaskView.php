<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Template;
use Setcooki\Wp\View;

class TaskView extends View
{
    /**
     * @param Template $template
     * @return mixed|string
     * @throws \Setcooki\Wp\Exception
     */
    public function render(Template $template)
    {
        if($template->has('id'))
        {
            $task = $this->controller->plugin->api->getTask($template->get('id'));
            $task->label = htmlentities(trim($task->title));
            $task->title = setcooki_linkify($task->title, '_blank');
            $template->add('task', $task);
            return $template->render();
        }
        return "";
    }
}