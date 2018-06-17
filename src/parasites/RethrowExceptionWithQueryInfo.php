<?php
namespace ParasitePDO\parasites;

use ParasitePDO\exceptions\SetterRequiredException;
use ParasitePDO\hosts\ParasitePDOException;
use ParasitePDO\formatters\IFormatExceptionMessage;
use ParasitePDO\hosts\ParasitePDO;

class RethrowExceptionWithQueryInfo implements IRethrowException
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
    
    public function setErrorInfo(array $errorInfo){}
    
    public function setDriverName(string $driverName){}
    
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
        
        $code = $this->PDOException->getCode();
        $FormatExceptionMessage = clone $this->FormatExceptionMessage;
        $FormatExceptionMessage->setPreviousExceptionMessage(
            $this->PDOException->getMessage()
        );
        $FormatExceptionMessage->setQueryString($this->statement);
        if (null !== $this->boundInputParams) {
            $FormatExceptionMessage
                ->setBoundInputParams($this->boundInputParams);
        }
        $FormatExceptionMessage->run();
        throw new ParasitePDOException(
            $FormatExceptionMessage->getFormattedExceptionMessage(),
            $code,
            $this->PDOException
        );
    }
    
    private $FormatExceptionMessage;
    public function setFormatExceptionMessage(
        IFormatExceptionMessage $FormatExceptionMessage
    )
    {
        $this->FormatExceptionMessage = $FormatExceptionMessage;
    }
}

