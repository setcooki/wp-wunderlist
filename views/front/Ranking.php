<?php

/**
 * Class Ranking
 */
class Ranking extends \Setcooki\Wp\Wunderlist\View\Front\Ranking
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