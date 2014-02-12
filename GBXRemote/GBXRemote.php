<?php namespace GBXRemote;
/**
 * This class enables you to connect with a Maniaplanet Server
 *
 * @author Jojo <jojo@zero-clan.org>
 */

use \Exception;

class GBXRemote {

    /**
     * The socket resource
     *
     * @var resource
     */
    private $socket;

    /**
     * A request and the matching response have the same handler
     *
     * @var int
     */
    private $handler = 0x80000000;

    /**
     * Creates a new GBXRemote instance
     *
     * @throw \Exception
     */
    public function __construct()
    {
        if(!extension_loaded("sockets"))
        {
            echo "PHP sockets extension required".PHP_EOL;

            exit(1);
        }

        if(!extension_loaded("xmlrpc"))
        {
            echo "PHP xmlrpc extension required".PHP_EOL;

            exit(1);
        }

        if(($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false)
        {
            throw new Exception(socket_strerror(socket_last_error()));
        }
    }

    /**
     * Initial connect to dedicated server
     *
     * @param string $ip
     * @param int $port
     * @return bool
     * @throws \Exception
     */
    public function connect($ip, $port)
    {
        if(($result = socket_connect($this->socket, $ip, $port)) === false)
        {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        if(($bytes = socket_recv($this->socket, $four, 4, MSG_WAITALL)) === false)
        {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        if(($bytes = socket_recv($this->socket, $protocol, 11, MSG_WAITALL)) === false)
        {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        if($protocol != "GBXRemote 2")
        {
            throw new Exception("Unsupported protocol: ".$protocol);
        }

        return true;
    }

    /**
     * Sends a request to the dedicated server and returns response
     *
     * @param string $methodName
     * @param mixed $arguments,...
     * @return mixed
     * @throws \Exception
     */
    public function query($methodName, $arguments = null)
    {
        $params = func_get_args();
        $method = array_shift($params);

        $xml = xmlrpc_encode_request($method, $params, array("encoding" => "utf-8", "escaping" => "cdata", "verbosity" => "no_white_space"));
        $tmp = $this->handler++;

        $bytes = pack('VVa*', strlen($xml), $tmp, $xml);

        if(($sent = socket_write($this->socket, $bytes)) === false)
        {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        if(($mix = socket_recv($this->socket, $mixbuffer, 8, MSG_WAITALL)) === false)
        {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        $array_result = unpack('Vsize/Vhandle', $mixbuffer);
        $size = $array_result['size'];
        $recvhandle = $array_result['handle'];

        if(($resp = socket_recv($this->socket, $respbuffer, $size, MSG_WAITALL)) === false)
        {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        $response = xmlrpc_decode($respbuffer, "utf-8");

        return $response;
    }

    /**
     * Handles dynamic calls to the obejct
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        switch (count($args))
        {
            case 0:
                return $this->query($name);
            case 1:
                return $this->query($name, $args[0]);

            case 2:
                return $this->query($name, $args[0], $args[1]);

            case 3:
                return $this->query($name, $args[0], $args[1], $args[2]);

            case 4:
                return $this->query($name, $args[0], $args[1], $args[2], $args[3]);

            default:
                array_unshift($args, $name);
                return call_user_func_array(array($this, "query"), $args);
        }
    }

    /**
     * Closes the socket
     *
     * @return void
     */
    public function close()
    {
        socket_close($this->socket);
    }
}