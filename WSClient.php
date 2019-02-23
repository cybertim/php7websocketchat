<?php

class WSClient
{

    private $socket;
    private $uniqid;
    private $displayName;

    public function __construct($client)
    {
        $this->socket = $client;
        $this->uniqid = uniqid();
        $this->displayName = 'Anonymous' . rand(100, 999);
    }

    /**
     * @return mixed
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @return string
     */
    public function getUniqid(): string
    {
        return $this->uniqid;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName)
    {
        $this->displayName = $displayName;
    }

}