<?php

/**
 * Class Standard
 */
class Standard extends \Setcooki\Wp\Wunderlist\View\Front\Standard
{
    /**
     * modify view behaviour or functionality by overwriting or extending this class/method. you can access controller
     * and base view with $this
     *
     * @param $params
     * @return mixed|string
     */
    public function execute($params)
    {
        return parent::execute($params);
    }
}