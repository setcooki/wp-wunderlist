<?php

namespace Setcooki\Wp\Wunderlist;

class Websocket
{
    /**
     * @var null|string
     */
    public $host = null;

    /**
     * @var int|null
     */
    public $port = null;

    /**
     * @var bool
     */
    public $ssl = false;

    /**
     * @var null
     */
    public $origin = null;

    /**
     * @var null
     */
    public $endpoint = null;

    /**
     * @var int
     */
    public $version = 13;

    /**
     * @var string
     */
    public $protocol = 'trigger';

    /**
     * @var string
     */
    public $transport = 'websocket';

    /**
     * @var null
     */
    public $socket = null;

    /**
     * @var string
     */
    public $guid = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';




    /**
     * @param $host
     * @param $port
     * @param bool $ssl
     * @param string $endpoint
     * @throws Exception
     */
    public function __construct($host, $port, $ssl = false, $origin = null, $endpoint = '/socket.io/?EIO=2')
    {
        $this->host = trim((string)$host);
        $this->port = (int)$port;
        $this->ssl = (bool)$ssl;
        $this->origin = (is_null($origin)) ? $_SERVER['SERVER_NAME'] : trim($origin);
        $this->endpoint = trim((string)$endpoint);
    }


    /**
     * @throws Exception
     */
    public function connect()
    {
        if(($this->socket = fsockopen($this->host, $this->port, $errno, $errstr)) === false)
        {
            throw new Exception(sprintf("socket error: %s, %d", $errstr, $errno));
        }
    }


    /**
     * @param $action
     * @param null $data
     * @return bool
     */
    public function emit($action, $data = null)
    {
        try
        {
            $action = trim((string)$action);
            $data   = (is_array($data)) ? json_encode($data) : (string)$data;
            $data   = $this->hybi10Encode('42["' . $action . '", "' . addslashes($data) . '"]');

            if(fwrite($this->socket, $data))
            {
                @fread($this->socket, 1000000);
                return true;
            }else{
                throw new Exception("emit: $action failed");
            }
        }
        catch(Exception $e)
        {
            throw new $e;
        }
    }


    /**
     * @param null $key
     * @return bool
     * @throws Exception
     */
    public function handshake($key = null)
    {
        if($key === null)
        {
            $key = $this->makeKey();
        }
        $endpoint = '/' . ltrim($this->endpoint, ' /');
        $transport = trim((string)$this->transport);

        $h = array();
        $h[] = "GET $endpoint&transport=$transport HTTP/1.1";
        $h[] = "Host: http" . (($this->ssl) ? "s" : "") . "://{$this->host}:{$this->port}";
        $h[] = "Upgrade: WebSocket";
        $h[] = "Connection: Upgrade";
        $h[] = "Sec-WebSocket-Key: {$key}";
        $h[] = "Sec-WebSocket-Version: {$this->version}";
        $h[] = "Sec-WebSocket-Protocol: {$this->protocol}";
        $h[] = "Origin: {$this->origin}";
        $h = trim(implode($h, "\r\n")) . "\r\n\r\n";

        if(fwrite($this->socket, $h))
        {
            if(($response = fread($this->socket, 1024)) !== false)
            {
                if(preg_match('/Sec\-WebSocket\-Accept:\s+(.*)$/imU', $response, $m))
                {
                    if(trim($m[1]) === base64_encode(pack('H*', sha1($key . $this->guid))))
                    {
                        return true;
                    }else{
                        throw new Exception("handshake failed");
                    }
                }else{
                    throw new Exception("response does not contain accept header");
                }
            }else{
                throw new Exception("got no response");
            }
        }else{
            throw new Exception("unable to write header");
        }
    }


    /**
     *
     */
    public function close()
    {
        fclose($this->socket);
        $this->socket = null;
    }


    /**
     * @param int $length
     * @return string
     */
    private function makeKey($length = 16)
    {
        $c = 0;
        $str = '';
        while($c++ * 16 < (int)$length)
        {
            $str .= md5(mt_rand(), true);
        }
        return base64_encode(substr($str, 0, $length));
    }


    /**
     * @param $payload
     * @param string $type
     * @param bool $masked
     * @return string
     */
    private function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $head = array();
        $mask = array();
        $type = strtolower(trim($type));
        $length = strlen($payload);
        
        switch($type)
        {
            case 'text':
                $head[0] = 129;
                break;
            case 'close':
                $head[0] = 136;
                break;
            case 'ping':
                $head[0] = 137;
                break;
            case 'pong':
                $head[0] = 138;
                break;
        }
        if ($length > 65535)
        {
            $lengthBin = str_split(sprintf('%064b', $length), 8);
            $head[1] = ($masked === true) ? 255 : 127;
            for($i = 0; $i < 8; $i++)
            {
                $head[$i + 2] = bindec($lengthBin[$i]);
            }
            if($head[2] > 127)
            {
                $this->close(1004);
                return false;
            }
        }else if($length > 125){
            $lengthBin = str_split(sprintf('%016b', $length), 8);
            $head[1] = ($masked === true) ? 254 : 126;
            $head[2] = bindec($lengthBin[0]);
            $head[3] = bindec($lengthBin[1]);
        }else{
            $head[1] = ($masked === true) ? $length + 128 : $length;
        }
        foreach(array_keys($head) as $i)
        {
            $head[$i] = chr($head[$i]);
        }
        if($masked === true)
        {
            for($i = 0; $i < 4; $i++)
            {
                $mask[$i] = chr(rand(0, 255));
            }
            $head = array_merge($head, $mask);
        }
        $frame = implode('', $head);
        for($i = 0; $i < $length; $i++)
        {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }
}