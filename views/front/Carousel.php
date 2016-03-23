<?php

/**
 * Class Carousel
 */
class Carousel extends \Setcooki\Wp\Wunderlist\View\Front\Carousel
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