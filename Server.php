<?php

include "WSHandler.php";
include "WSServer.php";
include "WSClient.php";
include "WSMessage.php";
include "ChatHandler.php";

/**
 * 15jaar gelden voor 't laatst in PHP gewerkt dus mijn hoop gevestigd op PHP7 en 't feit dat je nu wel normaal OO kan werken
 * Helaas is het niet zoals ik had gehoopt, nog steeds moet je de manual standaard open hebben en een hoop "bug-creep".
 * Gelukkig, met behulp van IDEA kom je al wat meer in de buurt (dankzij type-safety dat via comments is ingebouwd.. geeft je te denken..)
 */

$server = new  WSServer("127.0.0.1","1337");

$server->run(new ChatHandler());