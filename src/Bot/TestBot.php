<?php namespace hatxor\BotFramework;

use hatxor\BotFramework\Http;
use hatxor\BotFramework\Auth;
use hatxor\BotFramework\BotException;
use hatxor\BotFramework\Bot;

final class TestBot extends Bot {

    /**
     * [__construct description]
     * @param [type] $client [description]
     * @param [type] $secret [description]
     */
    public function __construct( $client, $secret ) {

        parent::__construct( $client, $secret );

    }


    /**
     * [pingEventHandler description]
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function pingEventHandler( $input ) {

        echo "pong";

    }

}