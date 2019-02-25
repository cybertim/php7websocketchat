<?php

include "WSHandler.php";
include "WSServer.php";
include "WSClient.php";
include "WSMessage.php";

include "ChatPersist.php";
include "ChatHandler.php";

$server = new  WSServer("127.0.0.1","4567");

$server->run(new ChatHandler());
