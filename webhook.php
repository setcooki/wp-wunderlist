<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/boot.php';

if(strtolower(trim($_SERVER['REQUEST_METHOD'])) === 'post' && isset($_REQUEST['token']) && isset($_REQUEST['notify']))
{
    try
    {
        $token = json_decode((string)@file_get_contents(setcooki_path('plugin') . '/var/token.json'), false);
        if($token && isset($token->webhook))
        {
            if(trim((string)$_REQUEST['token']) === $token->webhook)
            {
                $options = json_decode((string)@file_get_contents(setcooki_path('plugin') . '/var/options.json'), true);
                if($options)
                {
                    //get vars
                    $data = (isset($GLOBALS['HTTP_RAW_POST_DATA'])) ? json_decode($GLOBALS['HTTP_RAW_POST_DATA']) : null;
                    $action = (isset($_REQUEST['action'])) ? trim((string)$_REQUEST['action']) : null;
                    $notify = explode(':', trim((string)$_REQUEST['notify']));
                    $parent = strtolower(trim((string)$notify[0]));
                    $id = (int)$notify[1];

                    //clear cache
                    try
                    {
                        Setcooki\Wp\Cache::instance('c0')->forget("$parent:$id");
                    }
                    catch(\Exception $e){}

                    //execute webhook
                    if(isset($options['live']['enabled']) && (int)$options['live']['enabled'] === 1)
                    {
                        $host = (isset($options['live']['host'])) ? trim((string)$options['live']['host']) : setcooki_config('socket.client.default.host');
                        $port = (isset($options['live']['port'])) ? (int)$options['live']['port'] : setcooki_config('socket.client.default.port');

                        $websocket = new Setcooki\Wp\Wunderlist\Websocket($host, $port);
                        $websocket->connect();
                        $websocket->handshake();
                        $websocket->emit('trigger', Setcooki\Wp\Wunderlist\Webhook::compile($action, $data, array('parent' => $parent, 'id' => $id)));
                        $websocket->close();
                    }
                }else{
                    setcooki_log('unable to load options in webhook', LOG_WARNING);
                }
            }else{
                setcooki_log('unauthorized call to webhook', LOG_WARNING);
            }
        }else{
            setcooki_log('webhook token not set in token cache', LOG_WARNING);
        }
    }
    catch(Exception $e)
    {
        setcooki_log($e);
    }
}