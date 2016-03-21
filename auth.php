<?php

if(array_key_exists('REQUEST_URI', $_SERVER) && preg_match('/\?state\=([^\&]{1,})\&code\=([^\&]{1,}|$)/i', $_SERVER['REQUEST_URI'], $match))
{
    $err = '';
    if(!defined('DIRECTORY_SEPARATOR'))
    {
        define('DIRECTORY_SEPARATOR', ((isset($_ENV['OS']) && strpos('win', $_ENV["OS"]) !== false) ? '\\' : '/'));
    }
    $header = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wp-blog-header.php';
    if(!is_file($header) && array_key_exists('SCRIPT_FILENAME', $_SERVER))
    {
        $header = preg_replace('/(.*)(wp-content.*)/i', '$1wp-load.php', $_SERVER['SCRIPT_FILENAME']);
    }
    if(!is_file($header))
    {
        $header = preg_replace('/(.*)(wp-content.*)/i', '$1wp-load.php', dirname(__FILE__));
    }
    if(is_file($header))
    {
        define('BASE_PATH', substr($header, 0, strripos($header, DIRECTORY_SEPARATOR) + 1));
        define('WP_USE_THEMES', false);
        global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
        require_once $header;

        if(is_user_logged_in() && current_user_can('manage_options'))
        {
            $url = get_site_url() . '/wp-admin/options-general.php?' . http_build_query(array(
                'page' => 'wp_wunderlist_settings',
                'callback' => 1,
                'state' => trim($match[1]),
                'code' => trim($match[2])
            ));
            $headers = @get_headers($url);
            if($headers[0] !== 'HTTP/1.1 404 Not Found')
            {
                wp_redirect($url);
                if(!headers_sent())
                {
                    $err = 'redirect to plugin settings page failed';
                }
            }else{
                $err = 'can not resolve options url: ' . $url;
            }
        }else{
            $err = 'no logged in user or user with sufficient privileges found';
        }
    }else{
        $err = 'unable to find wp document root';
    }
}else{
    $err = 'invalid or missing request parameter';
}
echo sprintf('<pre>unable to redirect wunderlist oauth callback call due to: %1$s - please try again. if the error persist please file an issue at: <a href="%2$s">%2$s</a></pre>', $err, 'https://github.com/setcooki/wp-wunderlist/issues');