<?php

include "WSHandler.php";
include "WSServer.php";
include "WSClient.php";
include "WSMessage.php";

include "ChatPersist.php";
include "ChatHandler.php";

$server = new  WSServer("127.0.0.1","4567");

$server->run(new ChatHandler());


/**
 * Voetnoot;
 * 15jaar gelden voor 't laatst in PHP gewerkt, mijn hoop gevestigd op PHP7 en 't "nu wel" OO
 * Helaas, nog steeds moet je de manual standaard open hebben + een hoop "bug-creep".
 * Gelukkig, met behulp van Intellij kom je al wat meer in de buurt (dankzij type-safety dat via comments is ingebouwd, maar werkt niet 100%)
 */
