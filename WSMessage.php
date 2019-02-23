<?php

class WSMessage
{
    private $message;
    private $clients;

    /**
     * WSMessage constructor.
     * @param string $message
     * @param array|WSClient[] $clients
     */
    public function __construct(string $message, array $clients)
    {
        $this->message = $message;
        $this->clients = $clients;
    }

    /**
     * @return array|WSClient[]
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

}