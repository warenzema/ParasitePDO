<?php
namespace ParasitePDO\parasites;

use ParasitePDO\exceptions\SetterRequiredException;
use ParasitePDO\hosts\ParasitePDOException;

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
    
    public function run()
    {
        if (null === $this->PDOException) {
            throw new SetterRequiredException();
        }
        if (null === $this->statement) {
            throw new SetterRequiredException();
        }
        
        $code = $this->PDOException->getCode();
        throw new ParasitePDOException(
            $this->statement.$this->returnStringifiedBoundParams(),
            $code,
            $this->PDOException
        );
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

