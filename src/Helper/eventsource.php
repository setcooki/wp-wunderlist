<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

if(isset($_REQUEST['listen']))
{
    header('Content-Type: text/event-stream');
    header('Transfer-Encoding: identity');
    header('Cache-Control: no-cache');
    echo "retry: 100\n\n";

    $list = (int)$_REQUEST['listen'];
    $option = get_option('wunderlist_todo_notify', array());
    if(array_key_exists($list, $option) && array_key_exists(0, $option[$list]))
    {
        unset($option[$list][0]);
        update_option('wunderlist_todo_notify', $option);
        echo "data: do next\n\n";
    }else{
    }
    flush();
}

/*
 * this works!!!
 * event source get called only once and stays in infinite loop
 * inside loop can check for a incoming socket client connection
 */

/*
while(true)
{
    header('Content-Type: text/event-stream');
    header('Transfer-Encoding: identity');
    header('Cache-Control: no-cache');
    //echo "retry: 100\n\n";
    echo "data: ".time()."\n\n";
    flush();
    usleep(10000);
}


header('Content-Type: text/event-stream');
header('Transfer-Encoding: identity');
header('Cache-Control: no-cache');
//echo "retry: 100\n\n";
echo "data: ".time()."\n\n";
flush();
die();


$i = 0;
$ok = true;
$ip = 'localhost';
$port = 7777;
$start = time();
$server = false;

ob_start();
var_dump("start");
$o = ob_get_contents();
ob_end_clean();
file_put_contents("/var/www/vhosts/cooki.me/subdomain/start/test/hook.log", "$o\n", FILE_APPEND);

if($server === false)
{
    //server must only run once an can not be started for each client!
    $server = stream_socket_server("tcp://$ip:$port", $errno, $errmsg, 30);
}



if($server)
{
    while($ok)
    {
        $client = @stream_socket_accept($server);
        if($client)
        {
            ob_start();
            var_dump(fread($client, 1024));
            $o = ob_get_contents();
            ob_end_clean();
            file_put_contents("/var/www/vhosts/cooki.me/subdomain/start/test/client.log", "$o\n", FILE_APPEND);

            header('Content-Type: text/event-stream');
            header('Transfer-Encoding: identity');
            header('Cache-Control: no-cache');
            @fclose($client);
            //echo "retry: 100\n\n";
            echo "data: yes$i\n\n";

            usleep(10000);
        }else{
            /*header('Content-Type: text/event-stream');
            header('Transfer-Encoding: identity');
            header('Cache-Control: no-cache');
            echo "data: no$i\n\n";
            flush();
        }

        if(time() >= $start + 30)
        {
            $ok = false;
        }
        $i++;
    }
    @stream_socket_shutdown($server, STREAM_SHUT_RDWR);
    @fclose($server);
    $server = null;
    sleep(3);
    echo "data: close\n\n";
    flush();
}else{
    header('Content-Type: text/event-stream');
    header('Transfer-Encoding: identity');
    header('Cache-Control: no-cache');
    echo "data: {$errmsg}\n\n";
    flush();
}*/

?>