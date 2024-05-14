<?php
require_once __DIR__.'/../TestHelpers.php';
require_once __DIR__.'/../DbConnectionTrait.php';

use ParasitePDO\hosts\ParasitePDO;
use PHPUnit\Framework\TestCase;

class ParasitePDOStatementsFunctionAsParentTest extends TestCase
{
    use TestHelpers;
    use DbConnectionTrait;
    
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
        $query = "SELECT COUNT(*) FROM $tablename";

        $this->assertSame(
            $this->fetchColumnOfDirectQuery($PDO,$query),
            $this->fetchColumnOfDirectQuery($PDOObject,$query)
        );
    }

    private function fetchColumnOfDirectQuery($PDOObject,$query)
    {
        $statement = $PDOObject->query($query);

        return $statement->fetchColumn(0);
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

        $query = "SELECT COUNT(*) FROM $tablename";
        
        $this->assertSame(
            $this->fetchColumnOfPreparedQuery($PDO,$query),
            $this->fetchColumnOfPreparedQuery($PDOObject,$query)
        );
    }

    private function fetchColumnOfPreparedQuery($PDOObject,$query)
    {
        $statement = $PDOObject->prepare($query);

        $statement->execute();
        return $statement->fetchColumn(0);
    }
    
    private function returnInjectedParasitePDO()
    {
        return new ParasitePDO($this->returnRealPDO());
    }
    
    private function returnConstructedParasitePDO()
    {
        return new ParasitePDO($this->dsn,$this->username,$this->password);
    }

    private function returnRealPDO()
    {
        return new \PDO($this->dsn,$this->username,$this->password);
    }
}

