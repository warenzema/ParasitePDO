<?php
namespace ParasitePDO\parasites;

use PhpRun\CodeStyle\Exceptions\SetterRequiredException;
use ParasitePDO\formatters\IFormatExceptionMessage;
use ParasitePDO\hosts\ParasitePDO;

class RethrowConstraintViolationException implements IRethrowException
{
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
    
    private $errorInfo;
    public function setErrorInfo(array $errorInfo)
    {
        $this->errorInfo = $errorInfo;
    }
    
    private $driverName;
    public function setDriverName(string $driverName)
    {
        $this->driverName = $driverName;
    }
    
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
        if (null === $this->errorInfo) {
            throw new SetterRequiredException();
        }
        if (null === $this->driverName) {
            throw new SetterRequiredException();
        }
        
        $message = $this->PDOException->getMessage();
        $code = $this->PDOException->getCode();
        $rethrowExceptionClassName = false;
        if (0 === strpos((string)$code, '23')) {
            $rethrowExceptionClassName = 'ParasitePDO\exceptions\ConstraintViolationException';
        }
        if ('mysql' == $this->driverName && 1062 == $this->errorInfo[1]) {
            $rethrowExceptionClassName = 'ParasitePDO\exceptions\DuplicateKeyException';
        }
        
        if ($rethrowExceptionClassName) {
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

