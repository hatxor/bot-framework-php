<?php

use Microsoft\Bot\Bot;
use Microsoft\Bot\Config;
use Microsoft\Bot\Helpers;
use Microsoft\Bot\SkypeBot;
use Microsoft\Bot\FacebookBot;
use Microsoft\Bot\WebchatBot;
use Microsoft\Bot\EmailBot;
use Microsoft\Bot\TelegramBot;
use Microsoft\Bot\SlackBot;

/**
 * Here goes your methods to execute actions when the bot received
 * any event (a new message, a new contact, a new image, etc.)
 */
class MyBotClient {

    public $input;

    private $bot;

    private $hash;

    private $client;

    private $secret;


    /**
     * [__construct description]
     */
    public function __construct() {

        // 1. Load the config
        $this->loadConfig();

        // 2. We take the input data
        $this->input = $this->getPostRaw();

        // 3. Check the hash
        if( !isset( $_GET['hash'] ) || $_GET['hash'] != $this->hash )

            die("Unauthorized!"); // TODO Change by an exception

        // 4. Check the channel
        if( !isset( $this->input->channelId ) )

            die("Channel not found."); // TODO Change by an exception
            
        // 5. Init our bot depending of the channel
        $this->bot = Bot::getBotByChannel( $this->input->channelId, $this->client, $this->secret ); // TODO Try / catch to manage other errors

    }

    /**
     * [init description]
     * @return [type] [description]
     */
    public function init() {

        if( !isset( $this->input->type ) )

            die("Method not found."); // TODO Cambiar por una excepcion

        // Get the type of request and execute the right method
        return $this->fireEventHandler( $this->input->type );

    }


    /**
     * Load the configuration from the config.php file
     */
    private function loadConfig() {

        $this->hash = Options::get('hash');

        $this->client = Options::get('app_client_id');

        $this->secret = Options::get('app_secret_id');

    }


    /**
     * Get the info from the bot response
     * @param  boolean $to_object To enable if the response must ve given in object or in raw
     * @return mixed              String or Object depending. Response from the bot api.
     */
    private function getPostRaw( $to_object = true ) {

        $postRaw = file_get_contents('php://input');

        if( $to_object === true && Helpers::isJson( $postRaw ) === true )

            return json_decode( $postRaw );

        else

            return $postRaw;

    }


    /**
     * Manage the event firing the right method
     * @param  string $type Method to fire
     * @return mixed        The response of the selected method
     */
    private function fireEventHandler ( $type ) {

        $methodName = $type . 'EventHandler';

        $specificMethodName = $this->input->channelId . ucfirst($type) . 'EventHandler';

        if ( !method_exists( $this, $specificMethodName ) ) {

            if ( !method_exists( $this, $methodName ) ) {

                $botClassName = Bot::getBotName( $this->input->channelId );

                // If not exist in the client, we search for it in the Bot
                if ( !method_exists( $this->bot, $methodName ) )

                    die("No se encuentra el método!!"); // TODO Cambiar por una excepcion

                else

                    return $this->bot->$methodName( $this->input );

            }

            else

                return $this->$methodName();

        }

        else

            return $this->$specificMethodName();

    }

    /**
     * ######################################
     * YOU CAN CREATE FROM HERE
     * ######################################
     */


    /**
     * Default skype message event handler
     */
    public function skypeMessageEventHandler() {

        $to = $this->input->from->id;

        $message = "I have received \"" .  $message . "\" from " . $this->input->from->name . ".";

        $this->bot->addMessage( $to, $message );

        return 0;

    }


    /**
     * Default skype conversation update event handlers
     */
    public function skypeConversationUpdateEventHandler() {

        // Members added in group
        if( isset( $this->input->conversation->isGroup ) && $this->input->conversation->isGroup == true ) {

            $to = $this->input->conversation->id;

            if( isset( $this->input->membersAdded ) ) {

                $message = "Hi guys!! :)";

                $this->bot->addMessage( $to, $message );

            }

        }

        return 0;

    }

    /**
     * Default skype contact relation update event handlers
     * @return [type] [description]
     */
    public function skypeContactRelationUpdateEventHandler() {

        $to = $this->input->from->id;

        $name = explode(" ", $this->input->from->name)[0];

        $message = "Hey " . $name . "! What's up?? :)";

        $this->bot->addMessage( $to, $message );

        return 0;

    }

}
