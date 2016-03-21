<?php

namespace Setcooki\Wp\Wunderlist\View;

use Setcooki\Wp\Template;
use Setcooki\Wp\Option;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Wunderlist\Model\Model;

/**
 * Class ListView
 * @package Setcooki\Wp\Wunderlist\View
 */
class ListView extends View
{
    /**
     * @param Params $params
     * @return void
     */
    public function compile(Params $params)
    {
        $options = Option::get('wp_wunderlist_options', array());

        if($params->has('id'));
        {
            $id = (int)$params->get('id');
            if($params->get('cache'))
            {
                if(($cache = setcooki_cache("list:$id")) === false)
                {
                    $list = Model::getModel('Wunderlist')->get($id);
                    setcooki_cache("list:$id", $list, (($params->is('cache')) ? (int)$params->get('cache') : (int)setcooki_config('api.cache.lifetime')));
                    $this->assign('list', $list);
                }else{
                    $this->assign('list', $cache);
                }
            }else{
                $this->assign('list', Model::getModel('Wunderlist')->get($id));
            }
            if(array_key_exists('list', $options) && isset($options['list']['title_wrapper']) && !empty($options['list']['title_wrapper']))
            {
                $this->assign('title_wrapper', trim($options['list']['title_wrapper'], ' </\\\>'));
            }else{
                $this->assign('title_wrapper', 'div');
            }
        }
    }
}