<?php
namespace ParasitePDO\parasites;

use ParasitePDO\hosts\ParasitePDO;
use ParasitePDO\exceptions\SetterRequiredException;
use ParasitePDO\formatters\IFormatExceptionMessage;
use ParasitePDO\exceptions\LockWaitTimeoutException;

class RethrowLockWaitTimeoutException implements IRethrowException
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
    
    private $ParasitePDO;
    public function setParasitePDO(ParasitePDO $ParasitePDO)
    {
        $this->ParasitePDO = $ParasitePDO;
    }
    
    public function run()
    {
        if (null === $this->PDOException) {
            throw new SetterRequiredException();
        }
        if (null === $this->statement) {
            throw new SetterRequiredException();
        }
        if (null === $this->ParasitePDO) {
            throw new SetterRequiredException();
        }
        if (null === $this->FormatExceptionMessage) {
            throw new SetterRequiredException();
        }
        
        $errorInfo = $this->ParasitePDO->errorInfo();
        $driver = $this->ParasitePDO->getAttribute(\PDO::ATTR_DRIVER_NAME);
        //TODO PDOStatement has to give the error info
        if ('mysql' == $driver && 1205 == $errorInfo[1]) {
            $this->rethrowLockWaitTimeout();
        }
    }
    
    private function rethrowLockWaitTimeout()
    {
        $message = $this->PDOException->getMessage();
        $code = $this->PDOException->getCode();
        $additionalMessage = $this->generateAdditionalMessage();
            
        $FormatExceptionMessage = clone $this->FormatExceptionMessage;
        $FormatExceptionMessage->setPreviousExceptionMessage($message);
        $FormatExceptionMessage->setQueryString($this->statement);
        $FormatExceptionMessage->setAdditionalMessage($additionalMessage);
        if (null !== $this->boundInputParams) {
            $FormatExceptionMessage
                ->setBoundInputParams($this->boundInputParams);
        }
        $FormatExceptionMessage->run();
        
        throw new LockWaitTimeoutException(
            $FormatExceptionMessage->getFormattedExceptionMessage(),
            $code,
            $this->PDOException
        );
    }
    
    private function generateAdditionalMessage()
    {
        $Statement = $this->ParasitePDO
            ->query('SHOW ENGINE INNODB STATUS');
        $innoDbStatusRow = $Statement
            ->fetch(\PDO::FETCH_ASSOC);
        $innoDbStatus = $innoDbStatusRow['Status'];
        
        $Statement = $this->ParasitePDO
            ->query('SHOW FULL PROCESSLIST');
        $rawProcesslist = $Statement
            ->fetchAll(\PDO::FETCH_ASSOC);
        $processlist = print_r($rawProcesslist,true);
        
        return "InnoDb Status:\n\n$innoDbStatus\n\nFull Processlist:\n\n$processlist";
    }
    
    private $FormatExceptionMessage;
    public function setFormatExceptionMessage(
        IFormatExceptionMessage $FormatExceptionMessage
    )
    {
        $this->FormatExceptionMessage = $FormatExceptionMessage;
    }
}

