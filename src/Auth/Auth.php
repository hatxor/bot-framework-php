<?php namespace hatxor\BotFramework;

use hatxor\BotFramework\Http;

class Auth {

    private $http;

    public $token;

    private $authURL = "https://login.microsoftonline.com/common/oauth2/v2.0/token";


    /**
     * [__construct description]
     * @param [type] $client [description]
     * @param [type] $secret [description]
     */
    public function __construct( $client, $secret ) {

        $this->http = new Http;

        try {
            $this->getToken( $client, $secret );
        }

        catch (AuthException $e) {

            die(  __CLASS__ . ' Wuops! ' . $e->getMessage() );

        }

    }

    /**
     * [auth description]
     * @param  [type] $client [description]
     * @param  [type] $secret [description]
     * @return [type]         [description]
     */
    public function getToken( $client, $secret ) {

        $params = array(
            'grant_type' => 'client_credentials',
            'client_id' => $client,
            'client_secret' => $secret,
            'scope' => 'https://graph.microsoft.com/.default',
        );

        try {

            $httpResponse = $this->http->request( $this->authURL, 'POST', $params );

            if( $httpResponse['status'] == 200 ) {

                $httpResponseObj = json_decode( $httpResponse['result'] );

                if( isset( $httpResponseObj->access_token ) && $httpResponseObj->access_token != '' ) {

                    $this->token = $httpResponseObj->access_token;

                    return $this->token;

                }

                else

                    throw new AuthException( 'Error getting the access token!' );

            }

            throw new AuthException( 'Error getting the access token!' );

        } catch (HttpException $e) {

            if( $e->getCode() == 401 )

                throw new AuthException( '<strong>Unauthorized</strong> - The authentication information is not provided or is invalid.' );

            else if( $e->getCode() == 403 )

                throw new AuthException( '<strong>Forbidden</strong> - The provided credentials do not grant the client permission to access the resource. For example: a recognized user attempted to access restricted content..' );

            else

                die( $e->manageError() );

        }

    }

}