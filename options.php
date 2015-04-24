<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once dirname(__FILE__) . '/src/Setcooki/Wp/lib/core.php';

$options = null;

if(strtolower(trim($_SERVER['REQUEST_METHOD'])) === 'get' && isset($_REQUEST['nonce']))
{
    try
    {
        if(trim((string)$_REQUEST['nonce']) === get_option('wunderlist_nonce'))
        {
            $options = (string)@file_get_contents(setcooki_path('plugin') . '/var/options.json');
        }
    }
    catch(Exception $e){}
}

header('Content-type: application/json');
echo $options;
exit(0);