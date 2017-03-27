<?php namespace hatxor\BotFramework;

use hatxor\BotFramework\Helpers;
use hatxor\BotFramework\Http;
use hatxor\BotFramework\HttpException;
use hatxor\BotFramework\Auth;
use hatxor\BotFramework\AuthException;
use hatxor\BotFramework\BotException;
use hatxor\BotFramework\SkypeBot;
use hatxor\BotFramework\FacebookBot;
use hatxor\BotFramework\WebchatBot;
use hatxor\BotFramework\EmailBot;
use hatxor\BotFramework\TelegramBot;
use hatxor\BotFramework\SlackBot;

abstract class Bot {

    private $http;

    protected $client;

    protected $secret;

    protected $config;

    protected $token;

    protected $serviceUrl;

    protected $serviceVersion = "v3";

    protected $type_text_message = "message/text";

    protected $type_attachment = array( 

        "image" => "message/image", 
        "audio" => "message/audio", 
        "video" => "message/video", 
        "card" => "message/card.carousel", 

    );


    /**
     * [__construct description]
     * @param [type] $client [description]
     * @param [type] $secret [description]
     */
    public function __construct( $client, $secret, $config ) {

        $this->client = $client;

        $this->secret = $secret;

        $this->config = $config;

        $this->http = new Http();

    }


    /**
     * [getBotByChannel description]
     * @param  [type] $channelID [description]
     * @param  [type] $client    [description]
     * @param  [type] $secret    [description]
     * @return [type]            [description]
     */
    public static function getBotByChannel( $channelID, $client, $secret ) {

        $botClassName = self::getBotName( $channelID );

        if( !class_exists( $botClassName ) )

            throw new BotException( "Wuops! Not exist a bot for the channelId '" . $channelID . "' (".$botClassName.")", 1 );

        else {

            //file_put_contents('/var/www/cartasian/public/microsoft-bot/tmp/debug.txt', PHP_EOL . PHP_EOL . date("\[d-m-Y H:m:i\]") . ': '. $botClassName . PHP_EOL, FILE_APPEND );

            return new $botClassName( $client, $secret ); // TODO Lanzar excepcion si no se encuentra la clase

        }

    }


    /**
     * [getBotName description]
     * @param  [type] $channelID [description]
     * @return [type]            [description]
     */
    public static function getBotName( $channelID ) {

        return __NAMESPACE__ . "\\" . ucfirst( $channelID ) . 'Bot';

    }


    /**
     * [authenticate description]
     * @param  [type] $client [description]
     * @param  [type] $secret [description]
     * @return [type]         [description]
     */
    public function authenticate( $client = null, $secret = null ) {

        if( $client == null )

            $client = $this->client;

        if( $secret == null )

            $secret = $this->secret;

        $auth = new Auth( $client, $secret );

        $this->token = $auth->token;

        $this->http->permHeaders = array( 'Authorization: Bearer ' . $this->token );

    }


    /**
     * [authenticate description]
     * @param  [type] $client [description]
     * @param  [type] $secret [description]
     * @return [type]         [description]
     */
    protected function do_request( $url, $method = 'GET', $params = array(), $headers = array(), $secure = true, $excludePermHeaders = false, $debug = false ) {

        try {

            // We check by the auth
            if( $this->token == null)

                $this->authenticate();

            // Do request
            $result =  $this->http->request( $url, $method, $params, $headers, $secure, $excludePermHeaders, $debug );

            return $result;

        } catch (HttpException $e) {

            if( $e->getCode() == 401 )

                throw new AuthException( '<strong>Unauthorized</strong> - The authentication information is not provided or is invalid.' );

            else if( $e->getCode() == 403 )

                throw new AuthException( '<strong>Forbidden</strong> - The provided credentials do not grant the client permission to access the resource. For example: a recognized user attempted to access restricted content..' );

            else

                die( $e->manageError() );

        }

    }

    /**
     * COMMUNICATION METHODS
     */
    

    /**
     * [addMessage description]
     * @param  string $to      [description]
     * @param  string $message [description]
     * @return array           [description]
     */
    public function addMessage( $to, $message, $extra = array() ) {
        
        // Execute
        $url = $this->serviceUrl . '/' . $this->serviceVersion . '/conversations/' . $to . '/activities';

        $params = array(

            'type' => $this->type_text_message,
            "text" => $message,

        );

        $httpResponse = $this->do_request( $url, 'POST_RAW', $params, array( 'Content-Type: application/json' ) );

        return $httpResponse;
        
    }
    

    /**
     * [addAttachment description]
     * @param  string $to      [description]
     * @param  string $message [description]
     * @return array           [description]
     */
    public function addAttachment( $to, $type, $content, $extra = array(), $options = array() ) {

        if( !isset( $this->type_attachment[ $type ] ) )

            throw new BotException( "Wuops! The media type doesn't exist.", 1 );
        
        // Execute
        $url = $this->serviceUrl . '/' . $this->serviceVersion . '/conversations/' . $to . '/activities';

        $methodName = "_create" . $type;

        $params = array(

            'type' => $this->type_attachment[ $type ],
            "attachments" => $this->$methodName( $content, $extra, $options ),

        );

        if( isset( $options['summary'] ) )

            $params['summary'] = $options['summary'];

        $httpResponse = $this->do_request( $url, 'POST_RAW', $params, array( 'Content-Type: application/json' ) );

        return $httpResponse;
        
    }


    /**
     * [_createImage description]
     * @param [type] $content [description]
     * @param [type] $extra   [description]
     * @param [type] $options [description]
     */
    protected function _createImage( $content, $extra, $options ) {

        // Manipulate depending of the mode
        if( isset( $content['tmp_name'] ) ) {

            $imageEncoded = file_get_contents( $content['tmp_name'] ); 

            $mimeType = $content['type'];

            $imageEncoded = 'data:image/' . $mimeType . ';base64,' . base64_encode( $image );

            $response = array(

                "contentUrl" => $imageEncoded,
                "contentType" => $mimeType,

            );

        }

        else if( file_exists( $content ) ) {

            $fileInfo = new \finfo( FILEINFO_MIME_TYPE );

            $image = file_get_contents( $content );

            $mimeType = $fileInfo->buffer( $image );

            $imageEncoded = 'data:image/' . $mimeType . ';base64,' . base64_encode( $image );

            $response = array(

                "contentUrl" => $imageEncoded,
                "contentType" => $mimeType,

            );

        }

        else if( strpos( $content, 'http://' ) !== false || strpos( $content, 'https://' ) !== false ) {

            $fileInfo = new \finfo( FILEINFO_MIME_TYPE );

            $image = file_get_contents( $content );

            $mimeType = $fileInfo->buffer( $image );

            $imageEncoded = 'data:' . $mimeType . ';base64,' . base64_encode( $image );

            $response = array(

                "contentUrl" => $imageEncoded,
                "contentType" => $mimeType,

            );

        }

        else if( strpos( $content, 'data:image' ) !== false ) {

            $imageEncoded = $content;

            $mimeTypeAlt1 = strpos( $content, ';' );

            $mimeType = explode( ':', substr( $content, 0, $mimeTypeAlt1 ) )[1];

            $response = array(

                "contentUrl" => $content,
                "contentType" => $mimeType,

            );

        }

        else

            throw new BotException( "Wuops! The media type doesn't exist.", 1 );

        // Extra
        $response = array_merge( $response, $extra );

        return array( $response );

    }


    /**
     * [_createAudio description]
     * @param [type] $content [description]
     * @param [type] $extra   [description]
     * @param [type] $options [description]
     */
    protected function _createAudio( $content, $extra, $options ) {

        //TODO

    }


    /**
     * [_createVideo description]
     * @param [type] $content [description]
     * @param [type] $extra   [description]
     * @param [type] $options [description]
     */
    protected function _createVideo( $content, $extra, $options ) {

        //TODO

    }


    /**
     * [_createVideo description]
     * @param [type] $content [description]
     * @param [type] $extra   [description]
     * @param [type] $options [description]
     */
    protected function _createCard( $content, $extra, $options ) {

        if( !is_array( $content ) )

            $content = array( $content );

        if( !isset( $content[0] ) || !is_array( $content[0] ) || isset( $content['type'] ) )

            $content = array( $content );

        $final_response = array();

        foreach ($content as $key => $value) {

            //die( json_encode( $value ) );

            // General check
            if( !isset( $value['content'] ) || !is_array( $value['content'] ) || count( $value['content'] ) == 0 )

                throw new BotException( "Wuops! You need to provide an array with the content key.", 1 );

            if( !isset( $value['content']['title'] ) || $value['content']['title'] == '' )

                throw new BotException( "Wuops! You need to provide a title.", 1 );

            // Check by type
            if( $value['type'] == 'thumbnail' ) {

                // Image
                if( !isset( $value['content']['image'] ) || $value['content']['image'] == '' )

                    throw new BotException( "Wuops! In card thumbnail, you need to provide a image url.", 1 );

            }

            if( $value['type'] == 'hero' ) {

                // Text
                if( !isset( $value['content']['text'] ) || $value['content']['text'] == '' )

                    throw new BotException( "Wuops! In card hero, you need to provide a text.", 1 );

            }

            $response = array(

                'contentType' => 'application/vnd.microsoft.card.' . $value['type'], // thumbnail / hero / 
                'content' => array(

                    'title' => $value['content']['title'],

                ),

            );

            // Other values
            // Images
            if( isset( $value['content']['image'] ) ) {

                $response['content']['images'] = array();

                if( is_array( $value['content']['image'] ) ) {

                    $image = array( 'url' => $value['content']['image']['url'] );

                    if( isset( $value['content']['image']['alt'] ) )

                        $image['alt'] = $value['content']['image']['alt'];

                }

                else

                    $image = array( 'url' => $value['content']['image'] );

                $response['content']['images'][] = $image;

            }

            // Buttons
            if( isset( $value['content']['buttons'] ) ) {

                if( !is_array( $value['content']['buttons'] ) )

                    throw new BotException( "Wuops! The buttons of the card must be in an array.", 1 );

                $response['content']['buttons'] = array();

                foreach ($value['content']['buttons'] as $key => $button) {
                    
                    if( !is_array( $button ) || !isset( $button['type'] ) || !isset( $button['title'] ) || !isset( $button['value'] ) )

                        throw new BotException( "Wuops! Each button of the card must to have the fields type, titlle and value.", 1 );

                    $response['content']['buttons'][] = $button;

                }

            }

            // Text
            if( isset( $value['content']['text'] ) )

                $response['content']['text'] = $value['content']['text'];

            // Subtitle
            if( isset( $value['content']['text'] ) )

                $response['content']['subtitle'] = $value['content']['subtitle'];

            // Extra
            if( isset( $extra[$key] ) )

                $response['content'] = array_merge( $response['content'], $extra[$key] );

            $final_response[] = $response;

        }

        return $final_response;

    }

}
