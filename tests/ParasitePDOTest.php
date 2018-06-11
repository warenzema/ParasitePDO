<?php
namespace ParasitePDO;

use PHPUnit\Framework\TestCase;

class ParasitePDOTest extends TestCase
{
    private $dsn = 'mysql:host=localhost';
    private $username = 'dbuser';
    private $password = '123';
    private $dbname = 'parasitepdotest';
    
    public function providerPDOClassNames()
    {
        return [
            ['\PDO'],
            ['ParasitePDO\ParasitePDO']
        ];
    }
    
    /**
     * @dataProvider providerPDOClassNames
     * 
     * @testdox ParasitePDO instantiates the same way as a \PDO object instantiates, and is an instance of a \PDO object
     */
    
    public function testInstantiate($className)
    {
        $object = new $className($this->dsn,$this->username,$this->password);
        
        $this->assertInstanceOf($className, $object);
        
        $this->assertInstanceOf('\PDO',$object);
        
        $this->assertObjectCanQueryLikePDO($object);
    }
    
    /**
     * @testdox ParasitePDO can accept a \PDO object during construction instead of the normal arguments \PDO::__construct() uses
     */
    
    public function testCanInject()
    {
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $ParasitePDO = new ParasitePDO($PDO);
        
        $this->assertInstanceOf('\PDO',$ParasitePDO);
        
        $this->assertObjectCanQueryLikePDO($ParasitePDO);
    }
    
    public function testStatementIsParasiteStatement()
    {
        $dbname = 'db'.uniqid();
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $ParasitePDO = new ParasitePDO($PDO);
        
        $statement = $ParasitePDO->query("CREATE DATABASE IF NOT EXISTS $dbname");
        
        $this->assertInstanceOf('ParasitePDO\ParasitePDOStatement', $statement);
    }
    
    public function testInvalidStatementIsFalse()
    {
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $ParasitePDO = new ParasitePDO($PDO);
        
        $statement = $ParasitePDO->query("invalid statement");
        
        $this->assertFalse($statement);
    }
    
    public function testDuplicateKeyThrown()
    {
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $ParasitePDO = new ParasitePDO($PDO);
        
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        $ParasitePDO->exec("INSERT INTO $tablename (`id`) VALUES (1), (1)");
    }
    
    /**
     * @param \PDO $PDOObject
     */
    
    private function assertObjectCanQueryLikePDO($PDOObject)
    {
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $PDOObject->query("USE $this->dbname")->execute();
        $statement = $PDOObject->query("SELECT COUNT(*) FROM $tablename");
        
        $count = $statement->fetchColumn(0);
        
        $this->assertSame('0', $count);
    }
}

