<?php

namespace Setcooki\Wp\Wunderlist\View\Front;

use Setcooki\Wp\Template;
use Setcooki\Wp\Option;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Wunderlist\Model\Model;
use Setcooki\Wp\Wunderlist\View\View;

/**
 * Class Standard
 * @package Setcooki\Wp\Wunderlist\View\Front
 */
class Standard extends View
{
    /**
     * @param Params $params
     * @return void
     */
    public function compile(Params $params)
    {
        $options = Option::get('wp_wunderlist_options', array());

        if(array_key_exists('list', $options) && isset($options['list']['title_wrapper']) && !empty($options['list']['title_wrapper']))
        {
            $this->assign('list_title_wrapper', trim($options['list']['title_wrapper'], ' </\\\>'));
        }else{
            $this->assign('list_title_wrapper', 'div');
        }
        if(array_key_exists('task', $options) && isset($options['task']['title_wrapper']) && !empty($options['task']['title_wrapper']))
        {
            $this->assign('task_title_wrapper', trim($options['task']['title_wrapper'], ' </\\\>'));
        }else{
            $this->assign('task_title_wrapper', 'div');
        }
    }
}