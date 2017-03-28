<?php

use hatxor\BotFramework\Bot;
use hatxor\BotFramework\Helpers;
use hatxor\BotFramework\SkypeBot;
use hatxor\BotFramework\FacebookBot;
use hatxor\BotFramework\WebchatBot;
use hatxor\BotFramework\EmailBot;
use hatxor\BotFramework\TelegramBot;
use hatxor\BotFramework\SlackBot;

/**
 * Here goes your methods to send messages without reply one 
 * event (standalone)
 */
class MyBotServer {

    private $bot;

    private $hash;

    private $client;

    private $secret;


    /**
     * Load the configuration, create a new bot of the given type and do the login
     * @param string $channelID The channel ID
     */
    public function __construct( $channelID, $config = [] ) {

        // 1. Load the config
        $this->loadConfig( $config );

        // 2. Init our bot depending of the channel
        $this->bot = Bot::getBotByChannel( $channelID, $this->client, $this->secret, $config, $config ); // TODO Try / catch para controlar los errores de que no encuentre la clase

        // 3. Do the auth
        $this->bot->authenticate();

    }


    /**
     * Load the configuration from the config.php file
     */
    private function loadConfig( $config = [] ) {

        $this->hash = ( isset( $config['hash'] ) ) ? $config['hash'] : null;

        $this->client = ( isset( $config['app_client_id'] ) ) ? $config['app_client_id'] : null;

        $this->secret = ( isset( $config['app_secret_id'] ) ) ? $config['app_secret_id'] : null;

    }

    /**
     * ######################################
     * YOU CAN CREATE FROM HERE
     * ######################################
     */


    /**
     * Send a normal message with text to the given user
     * @param  string $to      Recipient ID
     * @param  string $message The message to send
     * @return array           HTTP response
     */
    public function sendMessage( $to, $message ) {

        return $this->bot->addMessage( $to, $message );

    }

}
