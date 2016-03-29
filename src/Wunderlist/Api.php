<?php

namespace Setcooki\Wp\Wunderlist\Wunderlist;

use Setcooki\Wp\Wunderlist\Exception;

class Api
{
    const GET                       = 'GET';
    const POST                      = 'POST';
    const PUT                       = 'PUT';
    const DELETE                    = 'DELETE';
    const PATCH                     = 'PATCH';

    const API_URL                   = 'API_URL';
    const API_VERSION               = 'API_VERSION';
    const API_AUTH_URL              = 'API_AUTH_URL';
    const API_ACCESS_TOKEN_URL      = 'API_ACCESS_TOKEN_URL';
    const CURL_HANDLE               = 'CURL_HANDLE';
    const AUTO_RESET                = 'AUTO_RESET';
    const PROXY_HOST                = 'PROXY_HOST';
    const PROXY_PORT                = 'PROXY_PORT';

    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * @var null
     */
    private $id = null;

    /**
     * @var null
     */
    private $secret = null;

    /**
     * @var null
     */
    private $token = null;

    /**
     * @var null
     */
    private $callback = null;

    /**
     * @var null|resource
     */
    public $curl = null;

    /**
     * @var array
     */
    public $options = array
    (
        self::API_VERSION           => 'v1',
        self::AUTO_RESET            => false
    );


    /**
     * @param null $options
     */
    public function __construct($options = null)
    {
        setcooki_init_options($options, $this);
        if(setcooki_has_option(self::CURL_HANDLE, $this))
        {
            $this->curl = setcooki_get_option(self::CURL_HANDLE, $this);
        }
    }


    /**
     *
     */
    protected function init()
    {
        if($this->curl === null)
        {
            $this->curl = curl_init();
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        }
    }


    /**
     * @param null $options
     * @return null|Api
     */
    public static function create($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }


    /**
     * @param $id
     */
    public function setClientId($id)
    {
        $this->id = trim((string)$id);
    }


    /**
     * @param $secret
     */
    public function setClientSecret($secret)
    {
        $this->secret = trim((string)$secret);
    }


    /**
     * @param $url
     */
    public function setCallbackUrl($url)
    {
        $this->callback = trim((string)$url);
    }


    /**
     * @return null
     */
    public function getCallbackUrl()
    {
        return $this->callback;
    }


    /**
     * @return string
     */
    public function getAuthLink($code)
    {
        if($this->id !== null && $this->callback !== null)
        {
            return trim(setcooki_get_option(self::API_AUTH_URL, $this), ' /?+#'). '?' . http_build_query(array('client_id' => $this->id, 'redirect_uri' => $this->callback, 'state' => $code));
        }
        return '';
    }


    /**
     * @param $code
     * @return string
     * @throws Exception
     */
    public function getAccessToken($code)
    {
        if($this->id !== null && $this->secret !== null && $code !== null)
        {
            $data = array
            (
                "client_id" => $this->id,
                "client_secret" => $this->secret,
                "code" => trim((string)$code)
            );
            try
            {
                $response = $this->call(setcooki_get_option(self::API_ACCESS_TOKEN_URL, $this), json_encode($data), self::POST);
                if(isset($response->access_token))
                {
                    return trim((string)$response->access_token);
                }else{
                    throw new Exception("access token not found in api response");
                }
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
                die();
            }
        }else{
            throw new Exception("missing client id, secret or auth code");
        }
    }


    /**
     * @param $token
     */
    public function setAccessToken($token)
    {
        $this->token = trim((string)$token);
    }


    /**
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getLists()
    {
        return $this->call("/lists");
    }


    /**
     * @param $id
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getList($id)
    {
        return $this->call("/lists/$id");
    }


    /**
     * @param $id
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function updateList($id, $data)
    {
        return $this->call("/lists/$id", $data, self::PATCH);
    }


    /**
     * @param $id
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getTask($id)
    {
        return $this->call("/tasks/$id");
    }


    /**
     * @param $id
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getTasks($id)
    {
        return $this->call("/tasks?list_id=$id");
    }


    /**
     * @param $id
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getCompletedTasks($id)
    {
        return $this->call("/tasks?list_id=$id&completed=true");
    }


    /**
     * @param $id
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function updateTask($id, $data)
    {
        return $this->call("/tasks/$id", $data, self::PATCH);
    }


    /**
     * @param $id
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function starTask($id, $data)
    {
        $data['starred'] = true;
        return $this->call("/tasks/$id", $data, self::PATCH);
    }


    /**
     * @param $id
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function unstarTask($id, $data)
    {
        $data['starred'] = false;
        return $this->call("/tasks/$id", $data, self::PATCH);
    }


    /**
     * @param $id
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function completeTask($id, $data)
    {
        $data['completed'] = true;
        return $this->call("/tasks/$id", $data, self::PATCH);
    }


    /**
     * @param $id
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function uncompleteTask($id, $data)
    {
        $data['completed'] = false;
        return $this->call("/tasks/$id", $data, self::PATCH);
    }


    /**
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function createTask($data)
    {
        return $this->call("/tasks", $data, self::POST);
    }


    /**
     * @param $id
     * @param string $for
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getComments($id, $for = 'task')
    {
        return $this->call("/task_comments?{$for}_id={$id}");
    }


    /**
     * @param $id
     * @param string $for
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getNotes($id, $for = 'task')
    {
        return $this->call("/notes?{$for}_id={$id}");
    }


    /**
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function createNote($data)
    {
        return $this->call("/notes", $data, self::POST);
    }


    /**
     * @param $id
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function updateNote($id, $data)
    {
        return $this->call("/notes/$id", $data, self::PATCH);
    }


    /**
     * @param $id
     * @param string $for
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getPositions($id, $for = 'task')
    {
        if(strtolower(trim($for)) == 'task')
        {
            return $this->call("/task_positions?list_id=$id");
        }else{
            return $this->call("/list_positions/$id");
        }
    }


    /**
     * @param $id
     * @param string $for
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function updatePositions($id, $for = 'task', $data)
    {
        return $this->call("/".strtolower(trim($for))."_positions/$id", $data, self::PUT);
    }


    /**
     * @param $id
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getWebhooks($id = null)
    {
        if($id !== null)
        {
            return $this->call("/webhooks?list_id=$id", null, self::GET, null, true);
        }else{
            try
            {
                $tmp = array();
                $data = $this->getLists();
                foreach((array)$data as $d)
                {
                    if(isset($d->list_type) && in_array(strtolower($d->list_type), array('list')))
                    {
                        try
                        {
                            $data = $this->getWebhooks($d->id);
                            if(!empty($data))
                            {
                                $tmp[$d->id] = $data;
                            }
                        }
                        catch(\Exception $e){}
                    }
                }
                return $tmp;
            }
            catch(\Exception $e){}
            return array();
        }
    }


    /**
     * @param $id
     * @param $url
     * @param null $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function createWebhook($id, $url, $data = null)
    {
        $data = (array)$data;
        $data['list_id'] = (int)$id;
        $data['url'] = trim((string)$url);
        if(!array_key_exists('processor_type', $data))
        {
            $data['processor_type'] = 'generic';
        }
        if(!array_key_exists('configuration', $data))
        {
            $data['configuration'] = '';
        }
        return $this->call("/webhooks", $data, self::POST, null, true);
    }


    /**
     * @param $id
     * @param $data
     * @return array|bool|mixed
     * @throws Exception
     */
    public function deleteWebhook($id, $data)
    {
        return $this->call("/webhooks/$id", $data, self::DELETE, null, true);
    }


    /**
     * @param null $id
     */
    public function deleteWebhooks($id = null)
    {
        try
        {
            //delete webhooks for a list
            if($id !== null)
            {
                try
                {
                    $data = $this->getWebhooks($id);
                }
                catch(\Exception $e) { $data = array(); }
                foreach((array)$data as $d)
                {
                    if(isset($d->url) && preg_match('='.preg_quote(setcooki_path('plugin', true, false)).'\/webhook\.php.*=i', trim($d->url)))
                    {
                        $this->deleteWebhook($d->id, array());
                    }
                }
            //delete all webhooks
            }else{
                $data = $this->getLists();
                foreach((array)$data as $d)
                {
                    if(isset($d->list_type) && in_array(strtolower($d->list_type), array('list')))
                    {
                        $this->deleteWebhooks($d->id);
                    }
                }
            }
        }
        catch(\Exception $e){}
    }


    /**
     * @param $id
     * @param string $for
     * @return array|mixed
     * @throws Exception
     */
    public function getFiles($id, $for = 'list')
    {
        if(strtolower(trim($for)) == 'task')
        {
            return $this->call("/files?task_id=$id");
        }else{
            return $this->call("/files?list_id=$id");
        }
    }


    /**
     * @param $url
     * @param null $data
     * @param string $method
     * @param null $options
     * @param bool $reset
     * @return array|mixed
     * @throws Exception
     */
    public function call($url, $data = null, $method = self::GET, $options = null, $reset = false)
    {
        $headers = array();

        $this->init();

        if(filter_var($url, FILTER_VALIDATE_URL) === false)
        {
            $url = trim(setcooki_get_option(self::API_URL, $this), ' /') . '/api/' . setcooki_get_option(self::API_VERSION, $this) . '/' . ltrim($url, ' /');
        }

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HEADER, false);

        $headers[] = 'Content-Type: application/json';
        if($this->token !== null)
        {
            $headers[] = 'X-Client-ID: ' . $this->id;
            $headers[] = 'X-Access-Token: ' . $this->token;
        }

        if(setcooki_has_option(self::PROXY_HOST, $this) && setcooki_get_option(self::PROXY_PORT, $this))
        {
            curl_setopt($this->curl, CURLOPT_PROXY, setcooki_get_option(self::PROXY_HOST, $this));
            curl_setopt($this->curl, CURLOPT_PROXYPORT, setcooki_get_option(self::PROXY_PORT, $this));
        }

        if($data !== null)
        {
            if(is_array($data))
            {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($this->normalize($data)));
            }else{
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, (string)$data);
            }
        }

        if($options !== null)
        {
            foreach((array)$options as $k => $v)
            {
                curl_setopt($this->curl, $k, $v);
            }
        }

        if(sizeof($headers) > 0)
        {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }

        if($method === self::POST)
        {
            curl_setopt($this->curl, CURLOPT_POST, true);
        }else if($method === self::DELETE){
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($this->curl, CURLOPT_POST, false);
        }else if($method === self::PUT){
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        }else if($method === self::PATCH){
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        }

        $response = curl_exec($this->curl);
        $code = (int)curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $err = curl_errno($this->curl);
        $msg = curl_error($this->curl);

        if(setcooki_get_option(self::AUTO_RESET, $this) || (bool)$reset)
        {
            $this->reset();
        }

        if($response !== false && $err === CURLE_OK)
        {
            if(($response = json_decode($response, false)) !== false)
            {
                if(isset($response->error))
                {
                    if(isset($response->error->message))
                    {
                        throw new Exception(vsprintf("api error: %s", array($response->error->message)));
                    }else{
                        throw new Exception('unknown api error');
                    }
                }else{
                    return $response;
                }
            }else{
                throw new Exception('unable to decode json response');
            }
        }else{
            throw new Exception(vsprintf("curl error: %d, %s", array($err, $msg)));
        }
    }


    /**
     *
     */
    protected function reset()
    {
        if($this->curl !== null)
        {
            curl_close($this->curl);
            $this->curl = null;
        }
    }


    /**
     * @param $data
     * @return array
     */
    protected function normalize($data)
    {
        if(!is_array($data))
        {
            $data = array($data);
        }
        foreach($data as $k => &$v)
        {
            if(ctype_digit($v))
            {
                $v = (int)$v;
            }
        }
        return (array_key_exists(0, $data) && sizeof($data) === 1) ? $data[0] : $data;
    }
}