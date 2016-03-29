<?php

namespace Setcooki\Wp\Wunderlist;

use Setcooki\Wp\Controller\Resolver;
use Setcooki\Wp\Option;
use Setcooki\Wp\Routing\Router;
use Setcooki\Wp\Wunderlist\Model\Model;
use Setcooki\Wp\Wunderlist\Wunderlist\Api;

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
     * @var null|Webhook
     */
    public $webhook = null;

    /**
     * @var null|Resolver
     */
    public $resolver = null;

    /**
     * @var null|Router
     */
    public $router = null;

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
        
        $this->api      = new Api(setcooki_config('api.options'));
        $this->admin    = new Admin($this, setcooki_config('admin.options'));
        $this->front    = new Front($this, setcooki_config('front.options'));
        $this->webhook  = new Webhook($this, setcooki_config('webhook.options'));

        Model::setApi($this->api);
        Model::setModel(setcooki_config('model.map'));

        $this->resolver = Resolver::create($this);
        $this->router   = Router::create($this);
    }


    /**
     *
     */
    public function init()
    {
        //test plugin capabilities
        $this->test();

        //make a auth code if not set
        if(!get_option('wp_wunderlist_auth_state', false))
        {
            add_option('wp_wunderlist_auth_state', substr(md5(rand()), 0, 16));
        }
        //set api client id
        if(($client_id = get_option('wp_wunderlist_client_id', false)) !== false)
        {
            $this->api->setClientId($client_id);
        }
        //set api client secret
        if(($client_secret = get_option('wp_wunderlist_client_secret', false)) !== false)
        {
            $this->api->setClientSecret($client_secret);
        }
        //set api access token
        if(($token = get_option('wp_wunderlist_access_token', false)) !== false)
        {
            $this->api->setAccessToken($token);
        }
        //set api callback url
        $this->api->setCallbackUrl(setcooki_path('plugin', true, true) . '/auth.php');

        //set default options
        if(!Option::has('wp_wunderlist_store'))
        {
            Option::set('wp_wunderlist_store', array());
        }

        //set webhook token
        if(!Option::has('wp_wunderlist_webhook_token'))
        {
            $this->webhook->tokenize();
        }

        //set config
        if(!is_file(setcooki_path('plugin'). '/var/config.json'))
        {
            file_put_contents(setcooki_path('plugin'). '/var/config.json', json_encode(setcooki_config('js.conf')));
        }

        $this->admin->init();
        $this->front->init();

        //get access token from api auth redirect and store
        if
        (
            is_admin() && (isset($_GET['page']) && $_GET['page'] === 'wp_wunderlist_settings')
            &&
            isset($_GET['code']) && (isset($_GET['callback']) && (int)$_GET['callback'] === 1)
        ){
            if((string)Option::get('wp_wunderlist_auth_state', '') === (string)$_GET['state'])
            {
                $token = $this->api->getAccessToken($_GET['code']);
                Option::save('wp_wunderlist_access_token', $token);
                Option::delete('wp_wunderlist_auth_state');
                wp_redirect('/wp-admin/options-general.php?page=wp_wunderlist_settings&token=1');
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
    public function activate()
    {

    }


    /**
     * delete plugin options and remove unused files
     *
     * @return void
     */
    public function deactivate()
    {
        $var = setcooki_path('plugin') . '/var/';
        if(is_file($var . '/options.json'))
        {
            @unlink($var . '/options.json');
        }
        if(is_file($var . '/token.json'))
        {
            @unlink($var . '/token.json');
        }
        setcooki_cache();
    }


    /**
     * on uninstall delete webhooks and options
     */
    public function uninstall()
    {
        $this->webhook->deleteAll();
        Option::delete('wp_wunderlist_*');
    }
}