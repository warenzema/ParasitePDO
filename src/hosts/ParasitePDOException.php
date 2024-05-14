<?php
namespace ParasitePDO\hosts;

class ParasitePDOException extends \PDOException
{
    public function __construct($message='',$code=0,$previous=null)
    {
        parent::__construct($message,(int)$code,$previous);
        $this->code = $code;
    }
}

