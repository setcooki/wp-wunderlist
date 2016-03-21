<?php

$client_id                  = get_option('wp_wunderlist_client_id', '');
$client_secret              = get_option('wp_wunderlist_client_secret', '');
$access_token               = get_option('wp_wunderlist_access_token', '');
$app_url                    = get_site_url();
$callback_url               = $this->wp->api->getCallbackUrl();
$options                    = get_option('wp_wunderlist_options', array());
$options                    = ((!empty($options)) ? $options : array());
$live_mode                  = (array_key_exists('live', $options) && isset($options['live']['mode'])) ? $options['live']['mode'] : 'none';
$live_poll_interval         = (array_key_exists('live', $options) && isset($options['live']['poll']['interval'])) ? $options['live']['poll']['interval'] : '';
$live_push_host             = (array_key_exists('live', $options) && isset($options['live']['push']['host'])) ? $options['live']['push']['host'] : '';
$live_push_port             = (array_key_exists('live', $options) && isset($options['live']['push']['port'])) ? $options['live']['push']['port'] : '';
$security_whitelist         = (array_key_exists('security', $options) && isset($options['security']['whitelist'])) ? $options['security']['whitelist'] : '';
$list_show_title            = (array_key_exists('list', $options) && isset($options['list']['show_title']));
$list_title_wrapper         = (array_key_exists('list', $options) && isset($options['list']['title_wrapper'])) ? trim($options['list']['title_wrapper']) : '';
$task_show_starred          = (array_key_exists('task', $options) && isset($options['task']['show_starred']));
$task_show_note             = (array_key_exists('task', $options) && isset($options['task']['show_note']));
$task_note_collapse         = (array_key_exists('task', $options) && isset($options['task']['note_collapse']));
$css_enabled                = (array_key_exists('css', $options) && isset($options['css']['enabled']));
$css_theme                  = (array_key_exists('css', $options) && isset($options['css']['theme'])) ? $options['css']['theme'] : rtrim(setcooki_get_option('THEME_PATH', $this->wp->front), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'default.less';
$admin_debug                = (array_key_exists('admin', $options) && isset($options['admin']['debug']));
$admin_log                  = (array_key_exists('admin', $options) && isset($options['admin']['log']));

if(setcooki_has_option('THEME_PATH', $this->wp->front, true))
{
    $path = rtrim(setcooki_get_option('THEME_PATH', $this->wp->front), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    foreach((array)@glob(setcooki_path('root') . $path . '*.less') as $css)
    {
        $themes[$path . basename($css)] = basename($css, '.less');
    }
}
if(setcooki_has_option('THEME_CUSTOM_PATH', $this->wp->front, true))
{
    $path = rtrim(setcooki_get_option('THEME_CUSTOM_PATH', $$this->wp->front), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    foreach((array)@glob(setcooki_path('root') . $path . '*.less') as $css)
    {
        $themes[$path . basename($css)] = basename($css, '.less');
    }
}
?>
<div class="wrap">
    <h2>WP Wunderlist Settings</h2>

    <?php if(isset($_GET['token']) && (int)$_GET['token'] === 1){ ?>
        <div id="setting-error-settings_updated" class="updated settings-error">
            <p>
                <strong>Access token successfully stored. You are ready to go.</strong>
            </p>
        </div>
    <?php } ?>

    <?php if(isset($_GET['message'])){ ?>
        <div id="setting-error-settings_updated" class="updated settings-error">
            <p>
                <strong><?php echo $_GET['message']; ?></strong>
            </p>
        </div>
    <?php } ?>

    <form id="wunderlist_settings" method="post" action="options.php">

        <?php @settings_fields('wp-wunderlist-setting-group'); ?>
        <?php @do_settings_sections('wp-wunderlist-setting-group'); ?>

        <?php if(empty($client_id)){ ?>
            <div class="error">Please add your API Client ID and Secret to start oAuth authentication</div>
        <? } ?>

        <table class="form-table">
            <!-- API SETTINGS //-->
            <tr valign="top">
                <th colspan="2"><h3 style="margin:0;">API Settings</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_app_url">App URL</label></th>
                <td>
                    <code><?php echo $app_url;  ?></code>
                    <p class="description">This link is your App URL - You need to store this URL in your wunderlist <a href="https://developer.wunderlist.com/applications" target="_blank">application manager</a></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_callback_url">API Callback URL</label></th>
                <td>
                    <code><?php echo $callback_url; ?></code>
                    <p class="description">This link is your Authorization Callback URL - You need to store this URL in your wunderlist <a href="https://developer.wunderlist.com/applications" target="_blank">application manager</a></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_client_id">API Client ID</label></th>
                <td><input type="text" name="wp_wunderlist_client_id" id="wp_wunderlist_client_id" size="64" value="<?php echo $client_id ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_client_secret">API Client Secret</label></th>
                <td><input type="text" name="wp_wunderlist_client_secret" id="wp_wunderlist_client_secret" size="64" value="<?php echo $client_secret; ?>" /></td>
            </tr>

            <?php if(!empty($client_id)){ ?>
                <tr valign="top">
                    <th scope="row"><label>oAuth2 Authentication</label></th>
                    <td>
                        <a class="button button-primary" id="wp_wunderlist_auth_button" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, array('action' => 'oauth-api'))) ?>">Authenticate</a>
                        <?php if(!empty($access_token)){ ?>
                            <span class="dashicons dashicons-yes" style="font-size:25px;color:#0073aa"></span><span class="description"> (Authenicated)</span>
                        <?php } ?>
                    </td>
                </tr>
            <? } ?>

            <!-- SECURITY OPTIONS //-->

            <tr valign="top" style="border-top: 1px solid #ddd;">
                <th colspan="2"><h3 style="margin:0;">Security Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_security_whitelist">Whitelist ID´s</label></th>
                <td>
                    <textarea name="wp_wunderlist_options[security][whitelist]" rows="5" cols="55" id="wp_wunderlist_security_whitelist" class="code"><?php echo $security_whitelist; ?></textarea>
                    <p class="description">Please enter every ID in a new line</p>
                </td>
            </tr>

            <!-- LIVE OPTIONS //-->
            <tr valign="top" style="border-top: 1px solid #ddd;">
                <th colspan="2"><h3 style="margin:0;">Live Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_live_mode">Select live mode</label></th>
                <td>
                    <div style="float:left">
                        <select name="wp_wunderlist_options[live][mode]" id="wp_wunderlist_options_live_mode" size="1">
                            <?php setcooki_dropdown(array('none' => 'none', 'poll' => 'poll', 'push' => 'push'), $live_mode); ?>
                        </select>
                    </div>
                    <div style="float:left;padding:5px">
                        <span class="description">Select a live mode or leave at "none"</span>
                    </div>
                </td>
            </tr>
            <tr valign="top" class="live poll <?php echo (($live_mode === 'poll') ? '' : 'hidden'); ?>">
                <th scope="row"><label for="wp_wunderlist_options_live_poll_interval">Poll interval in ms</label></th>
                <td>
                    <input type="text" name="wp_wunderlist_options[live][poll][interval]" id="wp_wunderlist_options_live_poll_interval" size="64" maxlength="6" value="<?php echo $live_poll_interval ?>" placeholder="5000"/>
                </td>
            </tr>
            <tr valign="top" class="live push <?php echo (($live_mode === 'push') ? '' : 'hidden'); ?>">
                <th scope="row"><label for="wp_wunderlist_options_live_push_host">Socket.io Host</label></th>
                <td><input type="text" name="wp_wunderlist_options[live][push][host]" id="wp_wunderlist_options_live_push_host" size="64" value="<?php echo $live_push_host ?>" placeholder="localhost"/></td>
            </tr>
            <tr valign="top" class="live push <?php echo (($live_mode === 'push') ? '' : 'hidden'); ?>">
                <th scope="row"><label for="wp_wunderlist_options_live_push_port">Socket.io Port</label></th>
                <td><input type="text" name="wp_wunderlist_options[live][push][port]" id="wp_wunderlist_options_live_push_port" size="64" value="<?php echo $live_push_port ?>" placeholder="7777" /></td>
            </tr>

            <!-- LIST OPTIONS //-->
            <tr valign="top" style="border-top: 1px solid #ddd;">
                <th colspan="2"><h3 style="margin:0;">List Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_list_show_title">Show list title</label></th>
                <td><input type="checkbox" name="wp_wunderlist_options[list][show_title]" id="wp_wunderlist_options_list_show_title" value="1" <?php checked($list_show_title ); ?> /> <span class="description">show/hide the list title</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_list_title_wrapper">List title wrapper</label></th>
                <td><input type="text" name="wp_wunderlist_options[list][title_wrapper]" id="wp_wunderlist_options_list_title_wrapper" size="16" value="<?php echo $list_title_wrapper; ?>" /> <span class="description">specify html title wrapper element (like h1, span, div, etc)</span></td>
            </tr>

            <!-- TASK OPTIONS //-->
            <tr valign="top" style="border-top: 1px solid #ddd;">
                <th colspan="2"><h3 style="margin:0;">Task Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_task_show_starred">Show task starred icon</label></th>
                <td><input type="checkbox" name="wp_wunderlist_options[task][show_starred]" id="wp_wunderlist_options_task_show_starred" value="1" <?php checked($task_show_starred); ?> /> <span class="description">show/hide the task starred icon</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_task_show_note">Show task note icon</label></th>
                <td><input type="checkbox" name="wp_wunderlist_options[task][show_note]" id="wp_wunderlist_options_task_show_note" value="1" <?php checked($task_show_note); ?> /> <span class="description">show/hide the task note icon</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_task_note_collapse">Auto collapse note</label></th>
                <td><input type="checkbox" name="wp_wunderlist_options[task][note_collapse]" id="wp_wunderlist_options_task_note_collapse" value="1" <?php checked($task_note_collapse); ?> /> <span class="description">show/collapse the task by default </span></td>
            </tr>

            <!-- STYLE OPTIONS //-->
            <tr valign="top" style="border-top: 1px solid #ddd;">
                <th colspan="2"><h3 style="margin:0;">Style Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_css_enabled">Enable css styles</label></th>
                <td><input type="checkbox" name="wp_wunderlist_options[css][enabled]" id="wp_wunderlist_options_css_enabled" value="1" <?php checked($css_enabled); ?> /> <span class="description">this will disable/enable css styling</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_css_theme">Select a theme</label></th>
                <td valign="top">
                    <div style="float:left">
                        <select name="wp_wunderlist_options[css][theme]" id="wp_wunderlist_options_css_theme" size="1">
                            <?php setcooki_dropdown($themes, $css_theme); ?>
                        </select>
                    </div>
                    <div style="float:left;padding:5px">
                        <span class="description">Select a css theme or none</span>
                    </div>
                </td>
            </tr>

            <!-- ADMIN OPTIONS //-->
            <tr valign="top" style="border-top: 1px solid #ddd;">
                <th colspan="2"><h3 style="margin:0;">Admin Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_admin_debug">Enable debug mode</label></th>
                <td><input type="checkbox" name="wp_wunderlist_options[admin][debug]" id="wp_wunderlist_options_admin_debug" value="1" <?php checked($admin_debug); ?> /> <span class="description">output debug/log messages on screen</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_wunderlist_options_admin_log">Enable logging</label></th>
                <td><input type="checkbox" name="wp_wunderlist_options[admin][log]" id="wp_wunderlist_options_admin_log" value="1" <?php checked($admin_log); ?> /> <span class="description">output debug/log messages on screen</span></td>
            </tr>

            <!-- ADMIN ACTIONS //-->
            <tr valign="top" style="border-top: 1px solid #ddd;">
                <th colspan="2"><h3 style="margin:0;">Admin Actions</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label>Reset API Webhooks</label></th>
                <td><a class="button button-primary" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, array('action' => 'webhook-reset'))) ?>">Reset</a></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label>Flush API Cache</label></th>
                <td><a class="button button-primary" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, array('action' => 'cache-flush'))) ?>">Flush</a></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label>Empty Log Files</label></th>
                <td><a class="button button-primary" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, array('action' => 'empty-logs'))) ?>">Empty</a></td>
            </tr>
        </table>

        <?php @submit_button(); ?>

    </form>
</div>

<?php

unset($app_url);
unset($options);
unset($client_id);
unset($callback_url);
unset($client_secret);
unset($live_mode);
unset($live_poll_interval);
unset($live_push_host);
unset($live_push_port);
unset($security_whitelist);
unset($list_show_title);
unset($list_title_wrapper);
unset($task_show_starred);
unset($task_show_note);
unset($task_note_collapse);
unset($css_enabled);
unset($css_theme);
unset($themes);
unset($admin_debug);
unset($admin_log);