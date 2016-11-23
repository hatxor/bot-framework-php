<?php namespace hatxor\BotFramework;

use Exception;

/**
 * Definir una clase de excepción personalizada
 */
class BotException extends Exception {

    /**
     * [__construct description]
     * @param [type]         $message  [description]
     * @param integer        $code     [description]
     * @param Exception|null $previous [description]
     */
    public function __construct( $message, $code = 0, Exception $previous = null ) {
        // algo de código
    
        // asegúrese de que todo está asignado apropiadamente
        parent::__construct($message, $code, $previous);
    }


    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}