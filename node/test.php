#!/usr/bin/php

<?php

if(strtolower(trim(php_sapi_name())) !== 'cli')
{
    echo "script can only be called from command line";
    exit(0);
}

require_once dirname(__FILE__) . '/../../../../wp-load.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$args = array();

if($argc >= 1)
{
    if($argc > 1)
    {
        for($i = 1; $i < $argc; $i++)
        {
            if(stripos($argv[$i], '--') !== false)
            {
                $argv[$i] = ltrim($argv[$i], '-');
                if(stripos($argv[$i], '=') !== false)
                {
                    $args[substr($argv[$i], 0, stripos($argv[$i], '='))] = str_replace(array(':', ','), array('=', '&'),substr($argv[$i], stripos($argv[$i], '=') + 1));
                }else{
                    $args[$argv[$i]] = null;
                }
            }
        }
    }

    if(array_key_exists('notify', $args) && array_key_exists('action', $args))
    {
        $token = (string)get_option('wunderlist_todo_webhook_token', '');
        $notify = str_replace('=', ':', $args['notify']);
        $action = (string)$args['action'];
        if(array_key_exists('data', $args))
        {
            $data = array();
            parse_str($args['data'], $data);
        }else{
            $data = array();
        }
        $data = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, setcooki_path('plugin', true, true) . "/webhook.php?token=$token&notify=$notify&action=$action");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        if(($data = curl_exec($ch)) !== false)
        {
            echo $data;
        }else{
            echo curl_error($ch);
        }
        curl_close($ch);
        exit(0);
    }
}