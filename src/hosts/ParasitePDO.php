<?php
namespace ParasitePDO\hosts;

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
            $this->rethrowCaughtException(
                $e,
                $statement
            );
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

    public function quote(string $string, int $type = \PDO::PARAM_STR)
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
        $this->setStatementClass();
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function query(string $query, ?int $fetchMode = null, ...$fetchModeArgs)
    {
        $this->setStatementClass();
        try {
            return call_user_func_array(
                [$this->instance,__FUNCTION__],
                func_get_args()
            );
        } catch (\PDOException $e) {
            $statement = func_get_arg(0);
            $this->rethrowCaughtException(
                $e,
                $statement
            );
        }
    }
    
    private function setStatementClass()
    {
        $this->instance->setAttribute(
            \PDO::ATTR_STATEMENT_CLASS,
            [
                'ParasitePDO\hosts\ParasitePDOStatement',
                [
                    $this,
                    $this->RethrowExceptions,
                ]
            ]
        );
    }
    
    private function rethrowCaughtException(
        \PDOException $PDOException,
        $statement
    )
    {
        foreach ($this->RethrowExceptions as $RethrowException) {
            $RethrowException->setPDOException($PDOException);
            $RethrowException->setStatement($statement);
            $RethrowException->setParasitePDO($this);
            $RethrowException->setErrorInfo($this->errorInfo());
            $RethrowException->setDriverName(
                $this->getAttribute(\PDO::ATTR_DRIVER_NAME)
            );
            $RethrowException->run();
        }
        throw $PDOException;
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

