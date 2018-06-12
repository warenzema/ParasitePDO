<?php
namespace ParasitePDO\parasites;

use ParasitePDO\exceptions\DuplicateKeyException;
use ParasitePDO\exceptions\SetterRequiredException;
use ParasitePDO\exceptions\ConstraintViolationException;

class RethrowConstraintViolationException implements IRethrowException
{
    public function construct(\PDOException $PDOException,string $statement)
    {
        $code = $PDOException->getCode();
        if ($code >= 23000 && $code < 24000) {
            $isMySQLDuplicateKeyException
                = false !== strpos(
                    $PDOException->getMessage(),
                    $this->mysqlDuplicateKeyString
                );
            if ($isMySQLDuplicateKeyException) {
                throw new DuplicateKeyException(
                    $statement,
                    $code,
                    $PDOException
                );
            }
        }
    }
    
    private $mysqlDuplicateKeyString = 'Integrity constraint violation: 1062 Duplicate entry';
    
    private $PDOException;
    public function setPDOException(\PDOException $PDOException)
    {
        $this->PDOException = $PDOException;
    }
    
    private $statement;
    public function setStatement(string $statement)
    {
        $this->statement = $statement;
    }
    
    public function run()
    {
        if (null === $this->PDOException) {
            throw new SetterRequiredException();
        }
        if (null === $this->statement) {
            throw new SetterRequiredException();
        }
        
        $message = $this->PDOException->getMessage();
        $code = $this->PDOException->getCode();
        $rethrowExceptionClassName = 'ParasitePDO\exceptions\ConstraintViolationException';
        if ($code >= 23000 && $code < 24000) {
            $isMySQLDuplicateKeyException
                = false !== strpos(
                    $message,
                    $this->mysqlDuplicateKeyString
                );
            if ($isMySQLDuplicateKeyException) {
                $rethrowExceptionClassName = 'ParasitePDO\exceptions\DuplicateKeyException';
            }
            throw new $rethrowExceptionClassName(
                $this->statement,
                $code,
                $this->PDOException
            );
        }
    }
}

