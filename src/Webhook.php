<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Logger;
use Setcooki\Wp\Option;

/**
 * Class Webhook
 * @package Setcooki\Wp\Wunderlist
 */
class Webhook
{
    /**
     * @var null
     */
    public $plugin = null;

    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    public $options = array();


    /**
     * @param Plugin $plugin
     * @param null $options
     */
    public function __construct(Plugin $plugin, $options = null)
    {
        $this->plugin = $plugin;
        setcooki_init_options($options, $this);
    }


    /**
     * @param Plugin $plugin
     * @param null $options
     * @return null|Webhook
     */
    public static function instance(Plugin $plugin, $options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($plugin, $options);
        }
        return self::$_instance;
    }


    /**
     * @param $id
     * @param $type
     */
    public function create($id, $type)
    {
        try
        {
            $id         = (int)$id;
            $type       = (string)$type;
            $token      = Option::get('wp_wunderlist_webhook_token', '');
            $url        = setcooki_path('plugin', true, true) . "/webhook.php?token=$token&notify=$type:$id";
            $hook       = "$type:$id";
            $hook_id    = null;
            $params     = array();

            if(($store = Option::has('wp_wunderlist_store.webhooks')) === false)
            {
                $store = Option::save('wp_wunderlist_store.webhooks', array());
            }

            if(array_key_exists('webhooks', $store) && !array_key_exists($hook, $store['webhooks']))
            {
                try
                {
                    $params = $this->plugin->api->getWebhooks($id);
                    if(is_object($params))
                    {
                        $params = array($params);
                    }
                }
                catch(Exception $e){}
                foreach((array)$params as $p)
                {
                    if(isset($p->url) && trim((string)$p->url) === $url)
                    {
                        $hook_id = (int)$p->id;
                        break;
                    }
                }
                if(is_null($hook_id))
                {
                    $params = $this->plugin->api->createWebhook($id, $url);
                    $hook_id = (int)$params->id;
                    Option::save("wp_wunderlist_store.webhooks.$hook", $hook_id);
                }
            }
        }
        catch(Exception $e)
        {
            setcooki_log($e);
        }
    }


    /**
     * @return string
     * @throws Exception
     */
    public function tokenize()
    {
        $cache = setcooki_path('plugin'). '/var/token.json';
        if(is_file($cache))
        {
            $params = (array)json_decode((string)@file_get_contents($cache));
        }else{
            $params = array();
        }
        $params['webhook'] = md5(uniqid(mt_rand(), true));
        if(file_put_contents(setcooki_path('plugin'). '/var/token.json', json_encode($params)))
        {
            Option::set('wp_wunderlist_webhook_token', $params['webhook']);
        }else{
            throw new Exception(setcooki_sprintf("token cache log file: %s could not be written", $cache));
        }
        return $params['webhook'];
    }

    /**
     * @param $id
     * @param string $type
     */
    public function delete($id, $type = 'list')
    {
        try
        {
            $id = (int)$id;
            $type = trim((string)$type);

            $this->plugin->api->deleteWebhooks($id);
            $options = (array)Option::get('wp_wunderlist_store', array());
            if(array_key_exists('webhooks', $options))
            {
                $tmp = array();
                foreach($options['webhooks'] as $k => $v)
                {
                    if(!preg_match("/^$type:$id$/i", $k))
                    {
                        $tmp[$k] = $v;
                    }
                }
                Option::save('wp_wunderlist_store.webhooks', $tmp);
            }
        }
        catch(Exception $e)
        {
            setcooki_log($e);
        }
    }


    /**
     *
     */
    public function deleteAll()
    {
        try
        {
            $this->plugin->api->deleteWebhooks();
            Option::delete('wp_wunderlist_store.webhooks');
            $this->tokenize();
        }
        catch(Exception $e)
        {
            setcooki_log($e);
        }
    }


    /**
     * @param null $action
     * @param null $data
     * @param $params
     * @return array
     */
    public static function compile($action = null, $data = null, $params)
    {
        //custom action called from test dev script
        if($action !== null)
        {
            $data = (array)$data;
            $action = trim((string)$action);
        //webhook called from wunderlist api
        }else if($data && isset($data->operation) && isset($data->subject)){
            $data = array_merge((array)$data->data, (array)$data->subject);
            $action = trim($data->operation) . ucfirst(trim($data->subject->type));
        //unknown webhook call
        }else{
            $data = null;
            $action = null;
        }
        if($action)
        {
            return array('action' => $action, 'data' => $data, 'params' => (array)$params);
        }else{
            //error
        }
    }
}