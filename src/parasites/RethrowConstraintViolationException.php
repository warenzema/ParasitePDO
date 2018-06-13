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
    
    private $boundInputParams;
    public function setBoundInputParams($boundInputParams)
    {
        $this->boundInputParams = $boundInputParams;
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
        if (is_numeric($code) && $code >= 23000 && $code < 24000) {
            $isMySQLDuplicateKeyException
                = false !== strpos(
                    $message,
                    $this->mysqlDuplicateKeyString
                );
            if ($isMySQLDuplicateKeyException) {
                $rethrowExceptionClassName = 'ParasitePDO\exceptions\DuplicateKeyException';
            }
            throw new $rethrowExceptionClassName(
                $this->statement.$this->returnStringifiedBoundParams(),
                $code,
                $this->PDOException
            );
        }
    }
    
    private function returnStringifiedBoundParams()
    {
        if (!is_array($this->boundInputParams)
            || empty($this->boundInputParams)
        ) {
            return "\n\nNo params were bound.";
        }
        
        $string = "\n\nBound with: ";
        $stringifiedParams = [];
        foreach ($this->boundInputParams as $key => $value) {
            $stringifiedParams[] = "'$key'=>'$value'";
        }
        $string .= implode(', ',$stringifiedParams);
        
        return $string;
    }
}

