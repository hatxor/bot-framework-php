<?php namespace hatxor\BotFramework;

class Helpers {
    
    public function __constructor() {

        

    }

    /**
     * [isJson description]
     * @param  [type]  $string [description]
     * @return boolean         [description]
     */
    public static function isJson( $string ) {

        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
    }

}