<?php

class ChatHandler implements WSHandler
{

    /**
     * @var WSMessage[]
     */
    private $queue = [];
    /**
     * @var WSClient[]
     */
    private $clients = [];
    /**
     * @var ChatPersist
     */
    private $persist;

    /**
     * ChatHandler constructor.
     */
    public function __construct()
    {
        $this->persist = new ChatPersist("chatlog");
    }


    public function onNewClient(WSClient $new_client)
    {
        // TODO: replay chatlog (persist)
        $this->clients[] = $new_client;
        $this->queue[] = new WSMessage(json_encode([
            "message" => $new_client->getDisplayName() . " joined the chatroom.",
            "join" => $new_client->getUniqid(),
            "displayName" => $new_client->getDisplayName()
        ]), $this->clients);
    }

    public function onLeaveClient(WSClient $leave_client)
    {
        unset($this->clients[array_search($leave_client, $this->clients)]);
        $this->queue[] = new WSMessage(json_encode(["message" => $leave_client->getDisplayName() . " left the chatroom."]), $this->clients);
    }


    public function onMessageReceived(WSClient $client, string $message)
    {
        $json = json_decode($message);
        if ($json->to === "channel") {
            // broadcast to all clients
            $this->queue[] = new WSMessage(json_encode(["message" => "<" . $client->getDisplayName() . "> " . $json->message]), $this->clients);
        } else {
            // private message for the receiver and sender eyes only
            foreach ($this->clients as $c) {
                if ($c->getUniqid() == $json->to) {
                    $this->queue[] = new WSMessage(json_encode(["message" => "[private] <" . $client->getDisplayName() . "> " . $json->message]), array($c, $client));
                }
            }
        }
    }

    /**
     * @return null|WSMessage
     */
    public function popMessage()
    {
        return array_pop($this->queue);
    }

}