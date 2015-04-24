<?php

$client_id                  = get_option('wunderlist_todo_client_id', '');
$client_secret              = get_option('wunderlist_todo_client_secret', '');
$app_url                    = get_site_url();
$callback_url               = $self->plugin->api->getCallbackUrl();
$options                    = get_option('wunderlist_todo_options', array());
$options                    = ((!empty($options)) ? $options : array());
$live_enabled               = (array_key_exists('live', $options) && isset($options['live']['enabled']));
$live_host                  = (array_key_exists('live', $options) && isset($options['live']['host'])) ? $options['live']['host'] : '';
$live_port                  = (array_key_exists('live', $options) && isset($options['live']['port'])) ? $options['live']['port'] : '';
$list_show_title            = (array_key_exists('list', $options) && isset($options['list']['show_title']));
$list_title_wrapper         = (array_key_exists('list', $options) && isset($options['list']['title_wrapper'])) ? trim($options['list']['title_wrapper']) : '';
$task_show_starred          = (array_key_exists('task', $options) && isset($options['task']['show_starred']));
$task_show_note             = (array_key_exists('task', $options) && isset($options['task']['show_note']));
$task_note_collapse         = (array_key_exists('task', $options) && isset($options['task']['note_collapse']));
$css_enabled                = (array_key_exists('css', $options) && isset($options['css']['enabled']));
$css_theme                  = (array_key_exists('css', $options) && isset($options['css']['theme'])) ? $options['css']['theme'] : rtrim(setcooki_get_option('THEME_PATH', $self->plugin->front), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'default.less';

if(setcooki_has_option('THEME_PATH', $self->plugin->front, true))
{
    $path = rtrim(setcooki_get_option('THEME_PATH', $self->plugin->front), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    foreach((array)@glob(setcooki_path('root') . $path . '*.less') as $css)
    {
        $themes[$path . basename($css)] = basename($css, '.less');
    }
}
if(setcooki_has_option('THEME_CUSTOM_PATH', $self->plugin->front, true))
{
    $path = rtrim(setcooki_get_option('THEME_CUSTOM_PATH', $self->plugin->front), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    foreach((array)@glob(setcooki_path('root') . $path . '*.less') as $css)
    {
        $themes[$path . basename($css)] = basename($css, '.less');
    }
}

?>

<div class="wrap">
    <h2>Wunderlist Todo Settings</h2>

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

        <?php @settings_fields('wunderlist-todo-setting-group'); ?>
        <?php @do_settings_fields('wunderlist-todo-setting-group'); ?>

        <?php if(empty($client_id)){ ?>
            <div class="error">Please add your API Client ID and Secret to start oAuth authentication</div>
        <? } ?>

        <table class="form-table">
            <tr valign="top">
                <th colspan="2"><h3 style="margin:0;">API Settings</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_app_url">App URL</label></th>
                <td>
                    <code><?php echo $app_url;  ?></code>
                    <p class="description">This link is your App URL - You need to store this URL in your wunderlist <a href="https://developer.wunderlist.com/applications" target="_blank">application manager</a></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_callback_url">API Callback URL</label></th>
                <td>
                    <code><?php echo $callback_url; ?></code>
                    <p class="description">This link is your Authorization Callback URL - You need to store this URL in your wunderlist <a href="https://developer.wunderlist.com/applications" target="_blank">application manager</a></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_client_id">API Client ID</label></th>
                <td><input type="text" name="wunderlist_todo_client_id" id="wunderlist_todo_client_id" size="64" value="<?php echo $client_id ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_client_secret">API Client Secret</label></th>
                <td><input type="text" name="wunderlist_todo_client_secret" id="wunderlist_todo_client_secret" size="64" value="<?php echo $client_secret; ?>" /></td>
            </tr>

            <?php if(!empty($client_id)){ ?>
                <tr valign="top">
                    <th scope="row"><label>oAuth2 Authentication</label></th>
                    <td><a class="button button-primary" id="wunderlist_todo_auth_button" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, array('action' => 'oauth-api'))) ?>">Authenticate</a></td>
                </tr>
            <? } ?>

            <tr valign="top">
                <th colspan="2"><h3 style="margin:0;">List Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_list_show_title">Show list title</label></th>
                <td><input type="checkbox" name="wunderlist_todo_options[list][show_title]" id="wunderlist_todo_options_list_show_title" value="1" <?php checked($list_show_title ); ?> /> <span class="description">show/hide the list title</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_list_title_wrapper">List title wrapper</label></th>
                <td><input type="text" name="wunderlist_todo_options[list][title_wrapper]" id="wunderlist_todo_options_list_title_wrapper" size="16" value="<?php echo $list_title_wrapper; ?>" /> <span class="description">specify html title wrapper element (like h1, span, div, etc)</span></td>
            </tr>

            <tr valign="top">
                <th colspan="2"><h3 style="margin:0;">Task Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_task_show_starred">Show task starred icon</label></th>
                <td><input type="checkbox" name="wunderlist_todo_options[task][show_starred]" id="wunderlist_todo_options_task_show_starred" value="1" <?php checked($task_show_starred); ?> /> <span class="description">show/hide the task starred icon</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_task_show_note">Show task note icon</label></th>
                <td><input type="checkbox" name="wunderlist_todo_options[task][show_note]" id="wunderlist_todo_options_task_show_note" value="1" <?php checked($task_show_note); ?> /> <span class="description">show/hide the task note icon</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_task_note_collapse">Auto collapse note</label></th>
                <td><input type="checkbox" name="wunderlist_todo_options[task][note_collapse]" id="wunderlist_todo_options_task_note_collapse" value="1" <?php checked($task_note_collapse); ?> /> <span class="description">show/collapse the task by default </span></td>
            </tr>

            <tr valign="top">
                <th colspan="2"><h3 style="margin:0;">Style Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_css_enabled">Enable Css Styles</label></th>
                <td><input type="checkbox" name="wunderlist_todo_options[css][enabled]" id="wunderlist_todo_options_css_enabled"  value="1" <?php checked($css_enabled); ?> /> <span class="description">this will disable/enable css styling</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_css_theme">Select a theme</label></th>
                <td valign="top">
                    <div style="float:left">
                        <select name="wunderlist_todo_options[css][theme]" id="wunderlist_todo_options_css_theme" size="1">
                            <?php setcooki_dropdown($themes, $css_theme); ?>
                        </select>
                    </div>
                    <div style="float:left;padding:5px">
                        <span class="description">Select a css theme or none</span>
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th colspan="2"><h3 style="margin:0;">Live Options</h3></th>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_live_enabled">Enable live/real-time mode</label></th>
                <td><input type="checkbox" name="wunderlist_todo_options[live][enabled]" id="wunderlist_todo_options_live_enabled"  value="1" <?php checked($live_enabled); ?> /> <span class="description">this will disable/enable real time updates</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_live_host">Socket.io Host</label></th>
                <td><input type="text" name="wunderlist_todo_options[live][host]" id="wunderlist_todo_options_live_host" size="64" value="<?php echo $live_host ?>" placeholder="localhost"/></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wunderlist_todo_options_live_port">Socket.io Port</label></th>
                <td><input type="text" name="wunderlist_todo_options[live][port]" id="wunderlist_todo_options_live_port" size="64" value="<?php echo $live_port ?>" placeholder="7777" /></td>
            </tr>

            <tr valign="top">
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
unset($live_enabled);
unset($live_host);
unset($live_port);
unset($list_show_title);
unset($list_title_wrapper);
unset($task_show_starred);
unset($task_show_note);
unset($task_note_collapse);
unset($css_enabled);
unset($css_theme);
unset($themes);