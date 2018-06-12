<?php

use ParasitePDO\hosts\ParasitePDO;
use PHPUnit\Framework\TestCase;

class ParasitePDOStatementsFunctionAsParentTest extends TestCase
{
    private $dsn = 'mysql:host=localhost';
    private $username = 'dbuser';
    private $password = '123';
    private $dbname = 'parasitepdotest';
    
    public function testReadOnlyQueryStringIsPopulated()
    {
        $ParasitePDO = new ParasitePDO($this->dsn,$this->username,$this->password);
        
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $ParasitePDO->query("USE $this->dbname")->execute();
        $queryString = "SELECT COUNT(*) FROM $tablename";
        $statement = $ParasitePDO->prepare($queryString);
        
        $this->assertEquals(
            $queryString,
            $statement->queryString
        );
    }
}

