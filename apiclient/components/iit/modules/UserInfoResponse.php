<?php

namespace app\components\iit\modules;

class UserInfoResponse
{
    public $issuer = null;
    public $issuercn = null;
    public $serial = null;
    public $subject = null;
    public $subjectcn = null;
    public $locality = null;
    public $state = null;
    public $o = null;
    public $ou = null;
    public $title = null;
    public $lastname = null;
    public $middlename = null;
    public $givenname = null;
    public $email = null;
    public $address = null;
    public $phone = null;
    public $dns = null;
    public $edrpoucode = null;
    public $drfocode = null;

    public function __construct(
        $response)
    {
        $this->issuer = $response['issuer'];
        $this->issuercn = $response['issuercn'];
        $this->serial = $response['serial'];
        $this->subject = $response['subject'];
        $this->subjectcn = $response['subjectcn'];
        $this->locality = $response['locality'];
        $this->state = $response['state'];
        $this->o = $response['o'];
        $this->ou = $response['ou'];
        $this->title = $response['title'];
        $this->lastname = $response['lastname'];
        $this->middlename = $response['middlename'];
        $this->givenname = $response['givenname'];
        $this->email = $response['email'];
        $this->address = $response['address'];
        $this->phone = $response['phone'];
        $this->dns = $response['dns'];
        $this->edrpoucode = $response['edrpoucode'];
        $this->drfocode = $response['drfocode'];
    }
}
