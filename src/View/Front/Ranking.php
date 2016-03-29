<?php

namespace Setcooki\Wp\Wunderlist\View\Front;

use Setcooki\Wp\Template;
use Setcooki\Wp\Option;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Wunderlist\Model\Model;
use Setcooki\Wp\Wunderlist\View\View;

/**
 * Class Ranking
 * @package Setcooki\Wp\Wunderlist\View\Front
 */
class Ranking extends Standard
{
	/**
    * @param Params $params
    * @return void
    */
    public function compile(Params $params)
    {
		parent::compile($params);
    }
}