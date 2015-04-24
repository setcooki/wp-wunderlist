<?php

if(preg_match('/\?state\=([^\&]{1,})\&code\=([^\&]{1,}|$)/i', $_SERVER['REQUEST_URI'], $m))
{
    require_once dirname(__FILE__) . '/../../../wp-load.php';

    if(is_user_logged_in() && current_user_can('manage_options'))
    {
        $path = get_site_url() . '/wp-admin/options-general.php?' . http_build_query(array(
            'page' => 'wunderlist_todo_settings',
            'callback' => 1,
            'state' => trim($m[1]),
            'code' => trim($m[2])
        ));
        wp_redirect($path);
        if(!headers_sent())
        {
            die();
        }
    }
}

echo "error";