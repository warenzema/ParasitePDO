<?php
namespace ParasitePDO\hosts;

class ParasitePDOStatement extends \PDOStatement
{
    
    private $RethrowExceptions = [];
    protected function __construct($pdo,$RethrowExceptions)
    {
        $this->RethrowExceptions = $RethrowExceptions;
    }
    
    public function execute($bound_input_params = NULL)
    {
        $args = func_get_args();
        try {
            return parent::execute(...$args);
        } catch (\Exception $e) {
            foreach ($this->RethrowExceptions as $RethrowException) {
                $RethrowException->setPDOException($e);
                $RethrowException->setStatement($this->queryString);
                $RethrowException->setBoundInputParams($bound_input_params);
                $RethrowException->run();
            }
            throw $e;
        }
    }
}

