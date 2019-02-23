<?php

class WSServer
{

    private static $wsMagicKey = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    private $server;
    /**
     * @var WSClient[]
     */
    private $clients = [];

    public function __construct($address, $port)
    {
        $this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->server, $address, $port);
        socket_listen($this->server);
        socket_set_nonblock($this->server);
    }

    public function __destruct()
    {
        foreach ($this->clients as $client) {
            socket_close($client->getSocket());
        }
        socket_close($this->server);
    }

    public function run(WSHandler $handlerer)
    {
        while (true) {
            if (($new_client = $this->checkNewClients()) !== false) $handlerer->onNewClient($new_client);
            foreach ($this->clients as $client) {
                if (($message = $this->receive($client)) !== null) $handlerer->onMessageReceived($client, $message);
            }
            while (($message = $handlerer->popMessage()) !== null) {
                foreach ($message->getClients() as $client) {
                    if (!$this->send($client, $message->getMessage())) {
                        $handlerer->onLeaveClient($client);
                        socket_close($client->getSocket());
                        unset($this->clients[array_search($client, $this->clients)]);
                    }
                }
            }
            sleep(1);
        }
    }

    private function checkNewClients()
    {
        if (($new_client = socket_accept($this->server)) !== false) {
            $this->doHandShake($new_client);
            $ws_client = new WSClient($new_client);
            $this->clients[] = $ws_client;
            return $ws_client;
        }
        return false;
    }

    private function doHandShake($socket)
    {
        $request = socket_read($socket, 2048);
        preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
        $key = base64_encode(pack('H*', sha1($matches[1] . WSServer::$wsMagicKey)));
        $headers = "HTTP/1.1 101 Switching Protocols\r\n";
        $headers .= "Upgrade: websocket\r\n";
        $headers .= "Connection: Upgrade\r\n";
        $headers .= "Sec-WebSocket-Version: 13\r\n";
        $headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";
        socket_write($socket, $headers, strlen($headers));
    }


    private function send(WSClient $client, string $text)
    {
        $encoded = $this->encodeFrame($text);
        if (socket_write($client->getSocket(), $encoded) !== false) return true;
        return false;
    }

    private function receive(WSClient $client)
    {
        if (($buffer = socket_read($client->getSocket(), 2048)) !== false) {
            return $this->decodeFrame($buffer);
        }
        return null;
    }

    private function decodeFrame($frame)
    {
        $len = ord($frame[1]) & 127;
        if ($len === 126) {
            $ofs = 8;
        } elseif ($len === 127) {
            $ofs = 14;
        } else {
            $ofs = 6;
        }
        $text = '';
        for ($i = $ofs; $i < strlen($frame); $i++) {
            $text .= $frame[$i] ^ $frame[$ofs - 4 + ($i - $ofs) % 4];
        }
        return $text;
    }

    private function encodeFrame(string $content)
    {
        $b = 129;
        $len = strlen($content);
        if ($len < 126) {
            return pack('CC', $b, $len) . $content;
        } elseif ($len < 65536) {
            return pack('CCn', $b, 126, $len) . $content;
        } else {
            return pack('CCNN', $b, 127, 0, $len) . $content;
        }
    }

}