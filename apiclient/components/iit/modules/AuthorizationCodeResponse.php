<?php

namespace app\components\iit\modules;

use common\helpers\Functions;

class AuthorizationCodeResponse
{
    public $access_token = null;
    public $token_type = null;
    public $expires_in = null;
    public $refresh_token = null;
    public $user_id = null;

    public function __construct($response)
    {
        Functions::log("CLIENT --- AuthorizationCodeResponse ");
        Functions::log($response);

        $this->access_token = $response['access_token'];
        $this->token_type = $response['token_type'];
        $this->expires_in = $response['expires_in'];
        $this->refresh_token = $response['refresh_token'];
        $this->user_id = $response['user_id'];
    }
}
