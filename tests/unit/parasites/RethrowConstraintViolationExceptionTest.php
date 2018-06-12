<?php
namespace ParasitePDO\unit\parasites;

use PHPUnit\Framework\TestCase;
use ParasitePDO\parasites\RethrowConstraintViolationException;
use ParasitePDO\hosts\ParasitePDO;

class RethrowConstraintViolationExceptionTest extends TestCase
{
    public function testDuplicateKeyThrown()
    {
        $this->markTestIncomplete();
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
    
    private $dsn = 'mysql:host=localhost';
    private $username = 'dbuser';
    private $password = '123';
    private $dbname = 'parasitepdotest';
    
    public function testDuplicateKeyThrownMessage()
    {
        $this->markTestIncomplete();
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $ParasitePDO = new ParasitePDO($PDO);
        try {
        $ParasitePDO->exec("INSERT INTO $tablename (`id`) VALUES (1), (1)");
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * @group setPDOException()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setPDOException()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetPDOException()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setPDOException']
        );
        
        $this->expectException('ParasitePDO\exceptions\SetterRequiredException');
        
        $SUT->run();
    }
    
    /**
     * @group setStatement()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setStatement()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetStatement()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setStatement']
        );
        
        $this->expectException('ParasitePDO\exceptions\SetterRequiredException');
        
        $SUT->run();
    }
    
    public function provider23000To23999()
    {
        return [
            [23000],
            [23100],
            [23500],
            [23999],
        ];
    }
    
    /**
     * @dataProvider provider23000To23999
     */
    
    public function testRunThrowsConstraintViolationIfIn23000CodeRange(
        $code   
    )
    {
        $PDOException = new \PDOException(
            uniqid(),
            $code,
            null
        );
        $statement = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        
        $this->expectException('ParasitePDO\exceptions\ConstraintViolationException');
        
        $SUT->run();
    }
    
    public function testIfExceptionThrownThenMessageIsStatement()
    {
        $PDOException = new \PDOException(
            uniqid(),
            23000,
            null
        );
        $statement = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        
        $this->expectException('ParasitePDO\exceptions\ConstraintViolationException');
        $this->expectExceptionMessage($statement);
        
        $SUT->run();
    }
    
    public function testConstraintViolationSetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            uniqid(),
            23000,
            null
        );
        $statement = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        
        try {
            $SUT->run();
        } catch (\Exception $e) {
            $this->assertSame(
                $PDOException,
                $e->getPrevious()
            );
        }
    }
    
    public function testIfPrevExceptionHasMysqlDupKeyThenThrowsDupKey()
    {
        $PDOException = new \PDOException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'",
            23000,
            null
        );
        $statement = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        $this->expectExceptionMessage($statement);
        
        $SUT->run();
    }
    
    public function testDupKeySetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'",
            23000,
            null
        );
        $statement = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        
        try {
            $SUT->run();
        } catch (\Exception $e) {
            $this->assertSame(
                $PDOException,
                $e->getPrevious()
            );
        }
    }
    
    private function returnSubjectUnderTest()
    {
        return new RethrowConstraintViolationException();
    }
    
    private function setRequiredSettersExceptAsSpecified(
        $SUT,
        array $specified
    )
    {
        if (!in_array('setPDOException',$specified)) {
            $SUT->setPDOException(new \PDOException());
        }
        if (!in_array('setStatement',$specified)) {
            $SUT->setStatement(uniqid());
        }
    }
}

