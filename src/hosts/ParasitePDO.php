<?php
namespace ParasitePDO\hosts;

use ParasitePDO\parasites\RethrowConstraintViolationException;
use ParasitePDO\parasites\IRethrowException;

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
            foreach ($this->RethrowExceptions as $RethrowException) {
                $RethrowException->setPDOException($e);
                $RethrowException->setStatement($statement);
                $RethrowException->run();
            }
            throw $e;
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
        $this->instance->setAttribute(
            \PDO::ATTR_STATEMENT_CLASS,
            [
                'ParasitePDO\hosts\ParasitePDOStatement',
                [
                    $this->instance,
                    $this->RethrowExceptions
                ]
            ]
        );
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function query()
    {
        $this->instance->setAttribute(
            \PDO::ATTR_STATEMENT_CLASS,
            ['ParasitePDO\hosts\ParasitePDOStatement',[$this->instance,[new RethrowConstraintViolationException()]]]
        );
        //TODO needs to be updated to follow what prepare() does
        //currently lacking covering test
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
    
    public function setRethrowExceptions(array $RethrowExceptions)
    {
        $this->RethrowExceptions = [];
        foreach ($RethrowExceptions as $RethrowException) {
            $this->addRethrowException($RethrowException);
        }
    }
    
    private $RethrowExceptions = [];
    public function getRethrowExceptions()
    {
        return $this->RethrowExceptions;
    }
    
    public function addRethrowException(
        IRethrowException $RethrowException
    )
    {
        $this->RethrowExceptions[] = $RethrowException;
    }
}

