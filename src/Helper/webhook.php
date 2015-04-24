<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once setcooki_path('plugin') . '/node/emitter.php';

print_r($_SERVER);

if(strtolower(trim($_SERVER['REQUEST_METHOD'])) === 'post' && isset($_REQUEST['notify']))
{
    $list = (int)$_REQUEST['notify'];
    $data = json_decode($HTTP_RAW_POST_DATA);
    $options = get_option('wunderlist_todo_options', array());

    if(isset($options['live']['enabled']) && (int)$options['live']['enabled'] === 1)
    {
        $host = (isset($options['live']['host'])) ? trim((string)$options['live']['host']) : null;
        $port = (isset($options['live']['port'])) ? (int)$options['live']['port'] : 80;

        $emitter = new SocketIoEmitter($host, $port);
    }
    /*ob_start();
    var_dump($socket->send('localhost', 7777, 'message'));
    $o = ob_get_contents();
    ob_end_clean();
    file_put_contents("/var/www/vhosts/cooki.me/subdomain/start/test/socket.log", "$o\n", FILE_APPEND);*/
}