<?php namespace hatxor\BotFramework;

class Options {

    /**
     * Retrieve the config file
     * @param mixed $key The key to find in the config
     * @return mixed         The value of the selected key or null
     */
    public static function get( $key ) {

        $configpath = realpath( __DIR__ . '/../../config/config.php' );

        $config = @include( $configpath );

        if( $config === null || $config === false || !is_array( $config ) )

            return null;

        if( array_key_exists( $key, $config ) )

            return $config[$key];

        else

            return null;

    }

}