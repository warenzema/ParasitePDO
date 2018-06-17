<?php
namespace ParasitePDO\hosts;

class ParasitePDOStatement extends \PDOStatement
{
    
    private $RethrowExceptions = [];
    private $ParasitePDO;
    protected function __construct(ParasitePDO $ParasitePDO,array $RethrowExceptions)
    {
        $this->ParasitePDO = $ParasitePDO;
        $this->RethrowExceptions = $RethrowExceptions;
    }
    
    public function execute($bound_input_params = NULL)
    {
        $args = func_get_args();
        try {
            return parent::execute(...$args);
        } catch (\PDOException $e) {
            foreach ($this->RethrowExceptions as $RethrowException) {
                $RethrowException->setPDOException($e);
                $RethrowException->setStatement($this->queryString);
                $RethrowException->setBoundInputParams($bound_input_params);
                $RethrowException->setParasitePDO($this->ParasitePDO);
                $RethrowException->setErrorInfo($this->errorInfo());
                $RethrowException->setDriverName(
                    $this->ParasitePDO->getAttribute(\PDO::ATTR_DRIVER_NAME)
                );
                $RethrowException->run();
            }
            throw $e;
        }
    }
}

