<?php
namespace ParasitePDO;

class ParasitePDO extends \PDO
{
    private $instance;
    
    public function __construct()
    {
        $arg = func_get_arg(0);
        if (is_object($arg) && $arg instanceof \PDO) {
            $this->instance = $arg;
        } else {
            $args = func_get_args();
            $this->instance = new \PDO(...$args);
        }
    }
    
    public function query()
    {
        $PDOStatement = call_user_func_array(
            [$this->instance,__FUNCTION__],
            func_get_args()
        );
        
        return new ParasitePDOStatement($PDOStatement);
    }
}

