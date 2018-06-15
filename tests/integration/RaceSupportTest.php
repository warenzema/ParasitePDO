<?php
use PHPUnit\Framework\TestCase;

class RaceSupportTest extends TestCase
{
    private $dsn = 'mysql:host=localhost';
    private $username = 'dbuser';
    private $password = '123';
    private $dbname = 'parasitepdotest';
    private $tablename = 'parasite_pdo_test_table';
    
    public function testDeadlock1()
    {
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $PDO->query("USE $this->dbname")->execute();
        
        $selectForUpdate1 = "SELECT id FROM $this->tablename WHERE id=1 FOR UPDATE";
        $selectForUpdate2 = "SELECT id FROM $this->tablename WHERE id=2 FOR UPDATE";
        
        $PDO->beginTransaction();
        $PDO->query($selectForUpdate2);
        $PDO->query($selectForUpdate1);
        sleep(1);
        $PDO->commit();
        
        $this->assertTrue(true,'Avoiding risky test warning');
    }
}

