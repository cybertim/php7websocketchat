<?php

interface WSHandler
{

    public function onNewClient(WSClient $new_client);

    public function onLeaveClient(WSClient $leave_client);

    public function onMessageReceived(WSClient $client, string $message);

    /**
     * @return null|WSMessage
     */
    public function popMessage();
}