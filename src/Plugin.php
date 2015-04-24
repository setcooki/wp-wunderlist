<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Option;

class Plugin extends \Setcooki\Wp\Plugin
{
    /**
     * @var null|Admin
     */
    public $admin = null;

    /**
     * @var null|Front
     */
    public $front = null;

    /**
     * @var null|Api
     */
    public $api = null;

    /**
     * @var array
     */
    public $options = array();


    /**
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->api = new Api(setcooki_config('wunderlist', 'api.options'));
        $this->admin = new Admin($this, setcooki_config('wunderlist', 'admin.options'));
        $this->front = new Front($this, setcooki_config('wunderlist', 'front.options'));
        $this->webhook = new Webhook($this);
    }


    /**
     *
     */
    public function init()
    {
        //test plugin capabilities
        $this->test();

        //make a auth code if not set
        if(!get_option('wunderlist_todo_auth_state', false))
        {
            add_option('wunderlist_todo_auth_state', substr(md5(rand()), 0, 16));
        }
        //set api client id
        if(($client_id = get_option('wunderlist_todo_client_id', false)) !== false)
        {
            $this->api->setClientId($client_id);
        }
        //set api client secret
        if(($client_secret = get_option('wunderlist_todo_client_secret', false)) !== false)
        {
            $this->api->setClientSecret($client_secret);
        }
        //set api access token
        if(($token = get_option('wunderlist_todo_access_token', false)) !== false)
        {
            $this->api->setAccessToken($token);
        }
        //set api callback url
        $this->api->setCallbackUrl(setcooki_path('plugin', true, true) . '/auth.php');

        //set default options
        if(!Option::has('wunderlist_todo_store'))
        {
            Option::set('wunderlist_todo_store', array());
        }

        //set webhook token
        if(!Option::has('wunderlist_todo_webhook_token'))
        {
            $this->webhook->tokenize();
        }

        $this->admin->init();
        $this->front->init();

        //get access token from api auth redirect and store
        if
        (
            is_admin() && (isset($_GET['page']) && $_GET['page'] === 'wunderlist_todo_settings')
            &&
            isset($_GET['code']) && (isset($_GET['callback']) && (int)$_GET['callback'] === 1)
        ){
            if((string)Option::get('wunderlist_todo_auth_state', '') === (string)$_GET['state'])
            {
                $token = $this->api->getAccessToken($_GET['code']);
                Option::save('wunderlist_todo_access_token', $token);
                Option::delete('wunderlist_todo_auth_state');
                wp_redirect('/wp-admin/options-general.php?page=wunderlist_todo_settings&token=1');
            }
        }
    }


    /**
     * @throws Exception
     */
    protected function test()
    {
        $var = setcooki_path('plugin') . '/var';
        if(!is_dir($var))
        {
            @mkdir($var, 0777);
        }
        if(!is_dir($var))
        {
            throw new Exception(setcooki_sprintf("cache/log dir: %s does not exist", $var));
        }
        if(!is_writeable($var))
        {
            throw new Exception(setcooki_sprintf("cache/log dir: %s is not writable", $var));
        }
    }


    /**
     *
     */
    public function activation()
    {

    }


    /**
     * delete plugin options and remove unused files
     *
     * @return void
     */
    public function deactivation()
    {
        $this->webhook->deleteAll();
        $var = setcooki_path('plugin') . '/var/';
        if(is_file($var . '/options.json'))
        {
            unlink($var . '/options.json');
        }
        if(is_file($var . '/token.json'))
        {
            unlink($var . '/token.json');
        }
        Option::delete('wunderlist_todo_*');
    }
}