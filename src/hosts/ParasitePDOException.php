<?php
namespace ParasitePDO\hosts;

class ParasitePDOException extends \PDOException
{
    public function __construct($message=null,$code=null,$previous=null)
    {
        parent::__construct($message,(int)$code,$previous);
        $this->code = $code;
    }
}

