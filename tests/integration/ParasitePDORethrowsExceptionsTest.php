<?php
use PHPUnit\Framework\TestCase;
use ParasitePDO\hosts\ParasitePDO;

class ParasitePDORethrowsExceptionsTest extends TestCase
{
    private $dsn = 'mysql:host=localhost';
    private $username = 'dbuser';
    private $password = '123';
    private $dbname = 'parasitepdotest';
    
    public function testDuplicateKeyThrownForQuery()
    {
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $ParasitePDO = new ParasitePDO($PDO);
        $query = "INSERT INTO $tablename (`id`) VALUES (1), (1)";
        $this->expectExceptionMessage($query);
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        $ParasitePDO->exec($query);
    }
    
    public function testDuplicateKeyThrownForPrepare()
    {
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $ParasitePDO = new ParasitePDO($PDO);
        $query = "INSERT INTO $tablename (`id`) VALUES (1), (1)";
        $Statement = $ParasitePDO->prepare($query);
        $this->expectExceptionMessage($query);
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        $Statement->execute();
    }
}

