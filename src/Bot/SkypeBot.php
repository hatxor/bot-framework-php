<?php namespace hatxor\BotFramework;

use hatxor\BotFramework\Http;
use hatxor\BotFramework\Auth;
use hatxor\BotFramework\BotException;
use hatxor\BotFramework\Bot;

final class SkypeBot extends Bot {

    protected $serviceUrl = "https://api.skype.net";

    protected $type_text_message = "message/text";

    protected $type_media_image = "message/image";

    protected $type_media_video = "message/video";

    protected $type_media_audio = "message/audio";

    protected $type_card = "message/card.carousel";

    protected $type_card_sign_in = "message/card.carousel";

    protected $type_card_receipt_card = "message/card.carousel";

    /**
     * [__construct description]
     * @param [type] $client [description]
     * @param [type] $secret [description]
     */
    public function __construct( $client, $secret ) {

        parent::__construct( $client, $secret );

    }

}