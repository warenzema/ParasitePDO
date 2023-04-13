<?php
namespace ParasitePDO\parasites;

use ParasitePDO\hosts\ParasitePDO;
use PhpRun\CodeStyle\Exceptions\SetterRequiredException;
use ParasitePDO\formatters\IFormatExceptionMessage;

class RethrowTransactionRollbackException implements IRethrowException
{
    private $mysqlDeadlockString = "Serialization failure: 1213 Deadlock found when trying to get lock";
    
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
        if (null === $this->ParasitePDO) {
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
        $additionalMessage = false;
        if (0 === strpos((string)$code, '40')) {
            $rethrowExceptionClassName = 'ParasitePDO\exceptions\TransactionRollbackException';
        }
        if ('mysql' == $this->driverName && 1213 == $this->errorInfo[1]) {
            $rethrowExceptionClassName = 'ParasitePDO\exceptions\DeadlockException';
            $additionalMessage = $this->generateAdditionalMessage();
        }
        
        if ($rethrowExceptionClassName) {
            $FormatExceptionMessage = clone $this->FormatExceptionMessage;
            $FormatExceptionMessage->setPreviousExceptionMessage($message);
            $FormatExceptionMessage->setQueryString($this->statement);
            if ($additionalMessage) {
                $FormatExceptionMessage->setAdditionalMessage($additionalMessage);
            }
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

