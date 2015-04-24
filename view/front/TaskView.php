<?php

/**
 * Class TaskView
 */
class TaskView extends \Setcooki\Wunderlist\Todo\View\TaskView
{
    /**
     * modify view behaviour or functionality by overwriting or extending this class/method
     *
     * @param \Setcooki\Wp\Template $template
     * @return mixed|string
     */
    public function render(\Setcooki\Wp\Template $template)
    {
        return parent::render($template);
    }
}