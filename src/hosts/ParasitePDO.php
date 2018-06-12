<?php
namespace ParasitePDO\hosts;

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
        $this->instance->setAttribute(
            \PDO::ATTR_STATEMENT_CLASS,
            ['ParasitePDO\hosts\ParasitePDOStatement',[$this->instance]]
        );
    }
    
    public function beginTransaction()
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function commit()
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function errorCode()
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function errorInfo()
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function exec($statement)
    {
        try {
            return call_user_func_array(
                [$this->instance,__FUNCTION__],
                func_get_args()
            );
        } catch (\PDOException $e) {
            $Rethrow = new RethrowConstraintViolationException();
            $Rethrow->setPDOException($e);
            $Rethrow->setStatement($statement);
            $Rethrow->run();
        }
    }
    
    public function getAttribute($attribute)
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function inTransaction()
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function lastInsertId($seqname = NULL)
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function prepare($statement, $options = NULL)
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function query()
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function rollBack()
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function setAttribute($attribute,$value)
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
}

