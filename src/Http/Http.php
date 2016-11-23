<?php namespace hatxor\BotFramework;

use hatxor\BotFramework\HttpException;

class Http {

    private $connection;

    private $debugFile = "/var/www/cartasian/public/microsoft-bot/tmp/debug.txt";

    public $permHeaders;

    /**
     * [__construct description]
     */
    public function __construct() {

        $this->connection = curl_init();

    }


    /**
     * [request description]
     * @param  [type]  $url    [description]
     * @param  string  $method [description]
     * @param  array   $params [description]
     * @param  boolean $secure [description]
     * @return [type]          [description]
     */
    public function request( $url, $method = 'GET', $params = array(), $headers = array(), $secure = true, $excludePermHeaders = false, $debug = false ) {

        curl_setopt( $this->connection, CURLOPT_URL, $url );
        curl_setopt( $this->connection, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $this->connection, CURLOPT_MAXREDIRS, 10 ); 
        curl_setopt( $this->connection, CURLOPT_FOLLOWLOCATION, 1 );

        $original_method = $method;

        // Secure opts
        if( $secure == false ) {
            curl_setopt( $this->connection, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt( $this->connection, CURLOPT_SSL_VERIFYPEER, 0 );
        }

        // Debug
        if( $debug === true ) {

            curl_setopt( $this->connection, CURLOPT_VERBOSE, true );
            $verbose = fopen( $this->debugFile, 'a+' );
            curl_setopt( $this->connection, CURLOPT_STDERR, $verbose );

        }

        // Headers
        if ( $excludePermHeaders == false && $this->permHeaders !== null && count( $this->permHeaders ) > 0 )

            $headers = array_merge( $headers, $this->permHeaders );

        if( isset( $headers ) && count( $headers ) > 0 )

            curl_setopt( $this->connection, CURLOPT_HTTPHEADER, $headers );

        // POST || PUT
        if( $method == 'POST' || $method == 'PUT' ) {

            curl_setopt( $this->connection, CURLOPT_CUSTOMREQUEST, $method );
            curl_setopt( $this->connection, CURLOPT_POST, count( $params ) );
            curl_setopt( $this->connection, CURLOPT_POSTFIELDS, http_build_query ( $params ) );

        }

        // POST RAW
        else if( $method == 'POST_RAW' ) {

            $params = json_encode ( $params );

            curl_setopt( $this->connection, CURLOPT_POSTFIELDS, $params );
            curl_setopt( $this->connection, CURLOPT_CUSTOMREQUEST, "POST" );

            //die( "Es: " . json_encode ( $headers ) );

        }

        // DELETE
        else if( $method == 'DELETE' ) {

            curl_setopt( $this->connection, CURLOPT_CUSTOMREQUEST, $method );
            curl_setopt( $this->connection, CURLOPT_POST, count( $params ) );
            curl_setopt( $this->connection, CURLOPT_POSTFIELDS, $fields_string );

        }

        // GET
        else {

            if( isset( $params ) && count( $params ) > 0 )

                curl_setopt( $this->connection, CURLOPT_URL, $url . '?' . $fields_string );

            else

                curl_setopt( $this->connection, CURLOPT_URL, $url );

        }

        // Exec
        $result = curl_exec( $this->connection );

        $http_code = curl_getinfo( $this->connection, CURLINFO_HTTP_CODE);

        if ( $errno = curl_errno( $this->connection ) || substr ( $http_code, 0, 1) != 2 ) {

            $error_params = array(

                'url' => $url,
                'method' => $original_method,
                'secure' => $secure,
                'headers' => $headers,
                'response' => $result,
                'http_code' => $http_code,
                'curl_string_error' => curl_strerror( $errno ),
                'params' => $params,
                'debug_mode' => $debug,

            );

            throw new HttpException( $error_params, $http_code );

        }

        else

            return array( 'result' => $result, 'status' => $http_code );

    }


    /**
     * [__destruct description]
     */
    public function __destruct() {

        curl_close( $this->connection );

        $this->connection = null;

    }


}