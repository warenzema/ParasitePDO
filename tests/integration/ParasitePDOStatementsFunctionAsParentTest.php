<?php
require_once __DIR__.'/../TestHelpers.php';

use ParasitePDO\hosts\ParasitePDO;
use PHPUnit\Framework\TestCase;

class ParasitePDOStatementsFunctionAsParentTest extends TestCase
{
    use TestHelpers;
    private $dsn = 'mysql:host=localhost';
    private $username = 'dbuser';
    private $password = '123';
    private $dbname = 'parasitepdotest';
    
    /**
     * @dataProvider providerTrueFalse2
     * 
     * @testdox ParasitePDOStatement objects returned from ParasitePDO::query() and ParasitePDO::prepare() function as would be expected of \PDOStatement objects from \PDO::query() and \PDO::prepare()
     */
    
    public function testParasitePDOReturnsStatementFromQueryAndPrepare(
        $injectPDOInsteadOfConstruct,
        $prepareInsteadOfQuery
    )
    {
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        
        if ($prepareInsteadOfQuery) {
            $this->assertObjectCanPrepareLikePDO($ParasitePDO);
        } else {
            $this->assertObjectCanQueryLikePDO($ParasitePDO);
        }
    }
    
    /**
     * @dataProvider providerTrueFalse2
     * 
     * @testdox ParasitePDOStatement->queryString is populated with statement passed to ParasitePDO::query() or ParasitePDO::prepare()
     */
    
    public function testReadOnlyQueryStringIsPopulated(
        $injectPDOInsteadOfConstruct,
        $prepareInsteadOfQuery
    )
    {
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $ParasitePDO->query("USE $this->dbname")->execute();
        $queryString = "SELECT COUNT(*) FROM $tablename";
        
        if ($prepareInsteadOfQuery) {
            $statement = $ParasitePDO->prepare($queryString);
        } else {
            $statement = $ParasitePDO->query($queryString);
        }
        
        $this->assertEquals(
            $queryString,
            $statement->queryString
        );
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
    
    /**
     * @param \PDO $PDOObject
     */
    
    private function assertObjectCanPrepareLikePDO($PDOObject)
    {
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $PDOObject->query("USE $this->dbname")->execute();
        $statement = $PDOObject->prepare("SELECT COUNT(*) FROM $tablename");
        
        $statement->execute();
        $count = $statement->fetchColumn(0);
        
        $this->assertSame('0', $count);
    }
    
    private function returnInjectedParasitePDO()
    {
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        return new ParasitePDO($PDO);
    }
    
    private function returnConstructedParasitePDO()
    {
        return new ParasitePDO($this->dsn,$this->username,$this->password);
    }
}

