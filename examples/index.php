<?php

require_once __DIR__ . '/../vendor/autoload.php';

$to = "00:0000000a00c00000000ebd000ed000ab@thread.skype";

$server = new MyBotServer( 'skype' ); // Your active implementation of your bot

$server->sendMessage( $to, "Hey! What's up! Here goes a duck picture!" );

$server->sendImage( $to, 'http://www.publicdomainpictures.net/pictures/30000/t2/duck-on-a-rock.jpg' );