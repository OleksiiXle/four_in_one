<?php

namespace app\components\iit\modules;

class EnvelopedUserInfoResponse
{
    public $encryptedUserInfo = null;
    public $error = null;
    public $message = null;

    public function __construct($response)
    {
        $this->encryptedUserInfo = $response['encryptedUserInfo'];
        $this->error = $response['error'];
        $this->message = $response['message'];
    }
}
