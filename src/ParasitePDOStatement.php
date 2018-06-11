<?php
namespace ParasitePDO;

class ParasitePDOStatement extends \PDOStatement
{
    private $instance;
    
    public function __construct($PDOStatement)
    {
        $this->instance = $PDOStatement;
    }
    
    public function execute($bound_input_params = NULL)
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function fetchColumn($column_number = NULL)
    {
        return call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
    }
    
    public function getInstance()
    {
        return $this->instance;
    }
}

