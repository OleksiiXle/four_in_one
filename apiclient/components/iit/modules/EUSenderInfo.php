<?php

namespace app\components\iit\modules;

class EUSenderInfo
{

//================================================================================

    public $signTime			= null;
    public $useTSP				= false;
    public $ownerInfo			= null;

//================================================================================

    function __construct()
    {
        $ownerInfo = new EUOwnerInfo();
    }

//================================================================================

}