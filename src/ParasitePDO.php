<?php
namespace ParasitePDO;

use ParasitePDO\exceptions\DuplicateKeyException;
use ParasitePDO\parasites\RethrowConstraintViolationException;

class ParasitePDO extends \PDO
{
    private $instance;
    
    public function __construct()
    {
        $arg = func_get_arg(0);
        if (is_object($arg) && $arg instanceof \PDO) {
            $this->instance = $arg;
        } else {
            $args = func_get_args();
            $this->instance = new \PDO(...$args);
        }
    }
    
    public function query()
    {
        $PDOStatement = call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
        
        if (is_object($PDOStatement)) {
            return new ParasitePDOStatement($PDOStatement);
        } else {
            return $PDOStatement;
        }
    }
    
    public function exec($statement)
    {
        try {
            return call_user_func_array(
                [$this->instance,__FUNCTION__],
                func_get_args()
            );
        } catch (\PDOException $e) {
            new RethrowConstraintViolationException($e);
        }
    }
}

