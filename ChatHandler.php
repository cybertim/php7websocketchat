<?php

class ChatHandler implements WSHandler {

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
    public function __construct() {
        $this->persist = new ChatPersist("chatlog");
    }

    /**
     * 
     * @param WSClient $new_client
     */
    public function onNewClient(WSClient $new_client) {
        $chatmsg = $new_client->getDisplayName() . " joined the chatroom.";

        $this->queue[] = new WSMessage(json_encode([
                    "message" => $chatmsg,
                    "join" => $new_client->getUniqid(),
                    "displayName" => $new_client->getDisplayName()
                ]), $this->clients);

        // replay (20) history lines of public chat
        foreach ($this->persist->find(20) as $line) {
            $this->queue[] = new WSMessage(json_encode([
                        "message" => $line
                    ]), [$new_client]);
        }
        // replay all current active users
        foreach ($this->clients as $client) {
            $this->queue[] = new WSMessage(json_encode([
                        "join" => $client->getUniqid(),
                        "displayName" => $client->getDisplayName()]), [$new_client]);
        }
        // info for the client to know its own nickname
        $this->queue[] = new WSMessage(json_encode([
                    "info" => true,
                    "displayName" => $new_client->getDisplayName()]), [$new_client]);

        $this->clients[] = $new_client;
        $this->persist->persist($chatmsg);
    }

    public function onLeaveClient(WSClient $leave_client) {
        $chatmsg = $leave_client->getDisplayName() . " left the chatroom.";
        $this->persist->persist($chatmsg);
        $uid = $leave_client->getUniqid();
        unset($this->clients[array_search($leave_client, $this->clients)]);

        $this->queue[] = new WSMessage(json_encode([
                    "message" => $chatmsg,
                    "left" => $uid
                ]), $this->clients);
    }

    public function onMessageReceived(WSClient $client, string $message) {
        $json = json_decode($message);
        if ($json->to === "channel") {
            // broadcast to all clients
            $chatmsg = "<" . $client->getDisplayName() . "> " . $json->message;
            $this->persist->persist($chatmsg);
            $this->queue[] = new WSMessage(json_encode(["message" => $chatmsg]), $this->clients);
        } else {
            // private message for the receiver and sender eyes only
            $chatmsg = "[private] <" . $client->getDisplayName() . "> " . $json->message;
            // not persisted because it is private
            foreach ($this->clients as $c) {
                if ($c->getUniqid() == $json->to) {
                    $this->queue[] = new WSMessage(json_encode(["message" => $chatmsg]), [$c, $client]);
                }
            }
        }
    }

    /**
     * @return null|WSMessage
     */
    public function popMessage() {
        return array_pop($this->queue);
    }

}
