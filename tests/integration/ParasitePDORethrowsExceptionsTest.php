<?php

require_once __DIR__.'/../TestHelpers.php';

use PHPUnit\Framework\TestCase;
use ParasitePDO\hosts\ParasitePDO;
use ParasitePDO\parasites\RethrowConstraintViolationException;
use ParasitePDO\exceptions\DuplicateKeyException;

class ParasitePDORethrowsExceptionsTest extends TestCase
{
    use TestHelpers;
    private $dsn = 'mysql:host=localhost';
    private $username = 'dbuser';
    private $password = '123';
    private $dbname = 'parasitepdotest';
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @testdox DuplicateKeyException is thrown when using ParasitePDO::exec() with a statement that causes duplicate key exception, but only if RethrowConstraintVioldationException is added to ParasitePDO; else the normal PDOException is thrown
     */
    
    public function testDuplicateKeyThrownForExec(
        $addRethrowConstraintViolationException
    )
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
        if ($addRethrowConstraintViolationException) {
            $ParasitePDO->addRethrowException(new RethrowConstraintViolationException());
        }
        $exceptionCaught = false;
        $isDuplicateKeyException = null;
        try {
            $ParasitePDO->exec($query);
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $isDuplicateKeyException = $e instanceof DuplicateKeyException;
            $this->assertInstanceOf('PDOException', $e);
        }
        
        $this->assertTrue($exceptionCaught);
        
        $this->assertSame(
            $addRethrowConstraintViolationException,
            $isDuplicateKeyException
        );
    }
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @testdox DuplicateKeyException is thrown when using ParasitePDO::prepare() and then ParasitePDOStatement::execute() with a statement that causes duplicate key exception, but only if RethrowConstraintVioldationException is added to ParasitePDO; else the normal PDOException is thrown
     */
    
    public function testDuplicateKeyThrownForPrepare(
        $addRethrowConstraintViolationException
    )
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
        
        if ($addRethrowConstraintViolationException) {
            $ParasitePDO->addRethrowException(new RethrowConstraintViolationException());
        }
        $exceptionCaught = false;
        $isDuplicateKeyException = null;
        try {
            $Statement = $ParasitePDO->prepare($query);
            $Statement->execute();
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $isDuplicateKeyException = $e instanceof DuplicateKeyException;
        }
        
        $this->assertTrue($exceptionCaught);
        
        $this->assertSame(
            $addRethrowConstraintViolationException,
            $isDuplicateKeyException
        );
    }
    
    /**
     * @testdox DuplicateKeyException includes bound params if present
     */
    
    public function testDupKeyAddsBoundParams()
    {
        $tablename = 'parasite_pdo_test_table';
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $tablename")->execute();
        $PDO->query("CREATE TABLE $tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
        
        $ParasitePDO = new ParasitePDO($PDO);
        $ParasitePDO->addRethrowException(new RethrowConstraintViolationException());
        $query = "INSERT INTO $tablename (`id`) VALUES (1), (1), (:key3), (:key4)";
        $Statement = $ParasitePDO->prepare($query);
        
        $expectedExceptionMessage = "$query\n\nBound with: 'key3'=>'value3', 'key4'=>'value4'";
        
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        
        $Statement->execute(
            [
                'key3'=>'value3',
                'key4'=>'value4',
            ]
        );
    }
}

