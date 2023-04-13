<?php
namespace ParasitePDO\formatters;

use PhpRun\CodeStyle\Exceptions\SetterRequiredException;
use PhpRun\CodeStyle\Exceptions\ObjectNotRunYetException;

class FormatExceptionMessageWithMaximumInfo implements IFormatExceptionMessage
{
    private $previousExceptionMessage;
    public function setPreviousExceptionMessage(
        string $previousExceptionMessage
    )
    {
        $this->previousExceptionMessage = $previousExceptionMessage;
    }
    
    private $queryString;
    public function setQueryString(string $queryString)
    {
        $this->queryString = $queryString;
    }

    private $boundInputParams;
    public function setBoundInputParams($boundInputParams)
    {
        $this->boundInputParams = $boundInputParams;
    }

    private $additionalMessage;
    public function setAdditionalMessage(string $additionalMessage)
    {
        $this->additionalMessage = $additionalMessage;
    }

    public function run()
    {
        if (null === $this->previousExceptionMessage) {
            throw new SetterRequiredException();
        }
        if (null === $this->queryString) {
            throw new SetterRequiredException();
        }
        
        $this->formattedExceptionMessage
            = $this->previousExceptionMessage
            ."\n\nCaused by query:\n"
            .$this->queryString
            .$this->returnStringifiedBoundParams();
        
        if (null !== $this->additionalMessage) {
            $this->formattedExceptionMessage
                .= "\n\n$this->additionalMessage";
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
    
    private $formattedExceptionMessage;
    public function getFormattedExceptionMessage()
    {
        if (null === $this->formattedExceptionMessage) {
            throw new ObjectNotRunYetException();
        }
        return $this->formattedExceptionMessage;
    }
}

