<?php namespace hatxor\BotFramework;

use hatxor\BotFramework\CommonExceptionHandler;

/**
 * Definir una clase de excepción personalizada
 */
class HttpException extends CommonExceptionHandler {

    /**
     * [__construct description]
     * @param [type]         $message  [description]
     * @param integer        $code     [description]
     * @param Exception|null $previous [description]
     */
    public function __construct( $message, $code = 0, CommonExceptionHandler $previous = null ) {

        $this->alt_message = $message;

        parent::__construct( "Wuops! Error in curl call!", $code, $previous);

    }


    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    /**
     * [funciónPersonalizada description]
     * @return [type] [description]
     */
    public function manageError() {

        if( $this->getCode() == 400 )

            $this->alt_message['known_error'] = '<strong>Bad Request</strong> - The request can’t be fulfilled because of bad syntax.';

        else if( $this->getCode() == 401 )

            $this->alt_message['known_error'] = '<strong>Unauthorized</strong> - The authentication information is not provided or is invalid.';

        else if( $this->getCode() == 403 )

            $this->alt_message['known_error'] = '<strong>Forbidden</strong> - The provided credentials do not grant the client permission to access the resource. For example: a recognized user attempted to access restricted content..';

        // Make the http error message
        $html = "<h3>".__CLASS__ ." Wuops! Curl error: ".$this->alt_message['curl_string_error']." [".$this->alt_message['http_code']."]</h3>";

        $html .= "<ul>";

        foreach ($this->alt_message as $field => $value) {
            
            if( is_numeric( $value ) || is_string( $value ) )

                $html .= "<li><strong>".$field."</strong>: ".$value."</li>";

            else if ( is_bool( $value ) ) {

                if( $value == true )

                    $html .= "<li><strong>".$field."</strong>: true</li>";

                else

                    $html .= "<li><strong>".$field."</strong>: false</li>";

            }

            else if ( is_object( $value ) || is_array( $value ) )

                $html .= "<li><strong>".$field."</strong>: ".json_encode($value)."</li>";

        }

        $html .= "</ul>";

        return $html;

    }

}