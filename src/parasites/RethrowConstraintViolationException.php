<?php
namespace ParasitePDO\parasites;

use ParasitePDO\exceptions\SetterRequiredException;
use ParasitePDO\formatters\IFormatExceptionMessage;
use ParasitePDO\hosts\ParasitePDO;

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
    
    public function setParasitePDO(ParasitePDO $ParasitePDO){}
    
    public function run()
    {
        if (null === $this->PDOException) {
            throw new SetterRequiredException();
        }
        if (null === $this->statement) {
            throw new SetterRequiredException();
        }
        if (null === $this->FormatExceptionMessage) {
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
            
            $FormatExceptionMessage = clone $this->FormatExceptionMessage;
            $FormatExceptionMessage->setPreviousExceptionMessage($message);
            $FormatExceptionMessage->setQueryString($this->statement);
            if (null !== $this->boundInputParams) {
                $FormatExceptionMessage
                    ->setBoundInputParams($this->boundInputParams);
            }
            $FormatExceptionMessage->run();
            
            throw new $rethrowExceptionClassName(
                $FormatExceptionMessage->getFormattedExceptionMessage(),
                $code,
                $this->PDOException
            );
        }
    }
    
    private $FormatExceptionMessage;
    public function setFormatExceptionMessage(
        IFormatExceptionMessage $FormatExceptionMessage
    )
    {
        $this->FormatExceptionMessage = $FormatExceptionMessage;
    }
}

