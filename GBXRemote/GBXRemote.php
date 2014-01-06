<?php namespace GBXRemote;
/**
 * This class enables you to connect with a Maniaplanet Server
 *
 * @author Jojo <jojo@zero-clan.org>
 */

class GBXRemote {

    private $socket;

    private $handler = 0x80000000;

    public function __construct()
    {
        if(($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false)
        {
            throw new Exception(socket_strerror(socket_last_error()));
        }
    }

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

    public function query()
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

    public function close()
    {
        socket_close($this->socket);
    }
}