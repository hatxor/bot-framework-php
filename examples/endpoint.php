<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new MyBotClient(); // The reactive implementation of your bot

$result = $client->init();