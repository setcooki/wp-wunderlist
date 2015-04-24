<?php

/**
 * Class ListView
 */
class ListView extends \Setcooki\Wunderlist\Todo\View\ListView
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