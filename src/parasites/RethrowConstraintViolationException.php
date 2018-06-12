<?php
namespace ParasitePDO\parasites;

use ParasitePDO\exceptions\SetterRequiredException;

class RethrowConstraintViolationException implements IRethrowException
{
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

