<?php

require_once __DIR__.'/../TestHelpers.php';

use PHPUnit\Framework\TestCase;
use ParasitePDO\hosts\ParasitePDO;
use ParasitePDO\exceptions\DuplicateKeyException;
use ParasitePDO\hosts\ParasitePDOException;
use ParasitePDO\parasites\RethrowConstraintViolationExceptionFactory;
use ParasitePDO\parasites\RethrowExceptionWithQueryInfoFactory;
use ParasitePDO\exceptions\DeadlockException;
use ParasitePDO\parasites\RethrowTransactionRollbackExceptionFactory;
use ParasitePDO\exceptions\LockWaitTimeoutException;
use ParasitePDO\parasites\RethrowLockWaitTimeoutException;
use ParasitePDO\parasites\RethrowLockWaitTimeoutExceptionFactory;

class ParasitePDORethrowsExceptionsTest extends TestCase
{
    use TestHelpers;
    private $dsn = 'mysql:host=localhost';
    private $username = 'dbuser';
    private $password = '123';
    private $dbname = 'parasitepdotest';
    private $tablename = 'parasite_pdo_test_table';
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->setupDatabaseAndTable();
    }
    
    /**
     * @dataProvider providerTrueFalse2
     * 
     * @testdox if RethrowConstraintVioldationException is added to ParasitePDO, then DuplicateKeyException is thrown when using ParasitePDO::exec() with a statement that causes duplicate key exception; else the normal PDOException is thrown if no rethrows are added
     */
    
    public function testDuplicateKeyThrownForExec(
        $injectPDOInsteadOfConstruct,
        $addRethrowConstraintViolationException
    )
    {
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        $ParasitePDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $ParasitePDO->query("USE $this->dbname")->execute();
        
        $query = "INSERT INTO $this->tablename (`id`) VALUES (1), (1)";
        if ($addRethrowConstraintViolationException) {
            $ParasitePDO->addRethrowException(
                (new RethrowConstraintViolationExceptionFactory())
                ->build()
            );
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
     * @dataProvider providerTrueFalse2
     * 
     * @testdox if RethrowConstraintVioldationException is added to ParasitePDO, then DuplicateKeyException is thrown when using ParasitePDO::prepare() and then ParasitePDOStatement::execute() with a statement that causes duplicate key exception; else the normal PDOException is thrown is no rethrows are added
     */
    
    public function testDuplicateKeyThrownForPrepare(
        $injectPDOInsteadOfConstruct,
        $addRethrowConstraintViolationException
    )
    {
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        $ParasitePDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $ParasitePDO->query("USE $this->dbname")->execute();
        
        $query = "INSERT INTO $this->tablename (`id`) VALUES (1), (1)";
        
        if ($addRethrowConstraintViolationException) {
            $ParasitePDO->addRethrowException(
                (new RethrowConstraintViolationExceptionFactory())
                ->build()
            );
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
     * @dataProvider providerTrueFalse1
     * 
     * @testdox DuplicateKeyException includes bound params if present
     */
    
    public function testDupKeyAddsBoundParams(
        $injectPDOInsteadOfConstruct
    )
    {
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        $ParasitePDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $ParasitePDO->query("USE $this->dbname")->execute();
        
        $ParasitePDO->addRethrowException(
            (new RethrowConstraintViolationExceptionFactory())
            ->build()
        );
        $query = "INSERT INTO $this->tablename (`id`) VALUES (1), (1), (:key3), (:key4)";
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
    
    /**
     * @dataProvider providerTrueFalse2
     * 
     * @testdox if RethrowExceptionWithQueryInfo is added to ParasitePDO, then ParasitePDOException is thrown when using ParasitePDO::query() with a statement that causes exception; else the normal PDOException is thrown if no rethrows are added
     */
    
    public function testRethrowWithQueryInfoWorksWithQuery(
        $injectPDOInsteadOfConstruct,
        $addRethrowWithQueryInfo
    )
    {
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        $ParasitePDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $ParasitePDO->query("USE $this->dbname")->execute();
        
        $query = "INSERT INTO $this->tablename (`no_such_column`) VALUES (1)";
        if ($addRethrowWithQueryInfo) {
            $ParasitePDO->addRethrowException(
                (new RethrowExceptionWithQueryInfoFactory())
                ->build()
            );
        }
        $exceptionCaught = false;
        $isParasitePDOException = null;
        $e = null;
        try {
            $ParasitePDO->query($query);
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $isParasitePDOException = $e instanceof ParasitePDOException;
            $this->assertInstanceOf('PDOException', $e);
        }
        
        $this->assertTrue($exceptionCaught);
        
        $this->assertSame(
            $addRethrowWithQueryInfo,
            $isParasitePDOException,
            $e
        );
    }
    
    /**
     * @dataProvider providerTrueFalse2
     * 
     * @testdox if RethrowExceptionWithQueryInfo is added to ParasitePDO, then ParasitePDOException is thrown when using ParasitePDO::prepare() and then ParasitePDOStatement::execute(); else the normal PDOException is thrown if no rethrows are added
     */
    
    public function testRethrowWithQueryInfoWorksWithPrepare(
        $injectPDOInsteadOfConstruct,
        $addRethrowWithQueryInfo
    )
    {
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        $ParasitePDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $ParasitePDO->query("USE $this->dbname")->execute();
        
        $query = "INSERT INTO $this->tablename (`no_such_column`) VALUES (1)";
        if ($addRethrowWithQueryInfo) {
            $ParasitePDO->addRethrowException(
                (new RethrowExceptionWithQueryInfoFactory())
                ->build()
            );
        }
        $exceptionCaught = false;
        $isParasitePDOException = null;
        $e = null;
        try {
            $Statement = $ParasitePDO->prepare($query);
            $Statement->execute();
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $isParasitePDOException = $e instanceof ParasitePDOException;
        }
        
        $this->assertTrue($exceptionCaught);
        
        $this->assertSame(
            $addRethrowWithQueryInfo,
            $isParasitePDOException,
            $e
        );
    }
    
    /**
     * @dataProvider providerTrueFalse3
     * 
     */
    
    public function testRethrowOnDeadlockWithQuery(
        $injectPDOInsteadOfConstruct,
        $addRethrowTransactionRollback,
        $usePrepareInsteadOfQuery
    )
    {
        $this->setupDatabaseAndTable();
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO1 = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO1 = $this->returnConstructedParasitePDO();
        }
        $ParasitePDO1->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $ParasitePDO1->query("USE $this->dbname")->execute();
        
        $query = "INSERT INTO $this->tablename (`id`) VALUES (1),(2),(3)";
        $ParasitePDO1->exec($query);
        
        $selectForUpdate1 = "SELECT id FROM $this->tablename WHERE id=1 FOR UPDATE";
        $selectForUpdate2 = "SELECT id FROM $this->tablename WHERE id=2 FOR UPDATE";
        if ($addRethrowTransactionRollback) {
            $ParasitePDO1->addRethrowException(
                (new RethrowTransactionRollbackExceptionFactory())
                ->build()
            );
        }
        $exceptionCaught = false;
        $isParasitePDOException = null;
        $e = null;
        
        $phpunit = "php ../vendor/bin/phpunit";
        
        $command = "$phpunit --filter testDeadlock1 integration/RaceSupportTest > /dev/null 2>&1 &";
        try {
            //need to not wait
            $ParasitePDO1->beginTransaction();
            $ParasitePDO1->query($selectForUpdate1);
            exec($command);
            sleep(1);
            if ($usePrepareInsteadOfQuery) {
                $Statement1 = $ParasitePDO1->prepare($selectForUpdate2);
                $Statement1->execute();
            } else {
                $ParasitePDO1->query($selectForUpdate2);
            }
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $isParasitePDOException = $e instanceof DeadlockException;
            $this->assertInstanceOf('PDOException', $e);
        }
        
        $this->assertTrue($exceptionCaught);
        
        $this->assertSame(
            $addRethrowTransactionRollback,
            $isParasitePDOException,
            $e
        );
    }
    
    /**
     * @dataProviders providerTrueFalse3
     * 
     */
    
    public function testRethrowOnLockWaitTimeout(
        $injectPDOInsteadOfConstruct=true,
        $addRethrowLockWaitTimeout=true,
        $usePrepareInsteadOfQuery=true
    )
    {
        $this->setupDatabaseAndTable();
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO1 = $this->returnInjectedParasitePDO();
            $ParasitePDO2 = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO1 = $this->returnConstructedParasitePDO();
            $ParasitePDO2 = $this->returnConstructedParasitePDO();
        }
        $ParasitePDO1->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $ParasitePDO1->query("USE $this->dbname")->execute();
        $ParasitePDO2->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $ParasitePDO2->query("USE $this->dbname")->execute();
        
        $query = "INSERT INTO $this->tablename (`id`) VALUES (1),(2),(3)";
        $ParasitePDO1->exec($query);
        
        $selectForUpdate1 = "SELECT id FROM $this->tablename WHERE id=1 FOR UPDATE";
        if ($addRethrowLockWaitTimeout) {
            $ParasitePDO2->addRethrowException(
                (new RethrowLockWaitTimeoutExceptionFactory())
                ->build()
            );
        }
        $exceptionCaught = false;
        $isParasitePDOException = null;
        $e = null;
        
        try {
            //need to not wait
            $ParasitePDO1->beginTransaction();
            $ParasitePDO1->query($selectForUpdate1);
            $ParasitePDO2->exec("SET SESSION innodb_lock_wait_timeout=1");
            if ($usePrepareInsteadOfQuery) {
                $Statement1 = $ParasitePDO2->prepare($selectForUpdate1);
                $Statement1->execute();
            } else {
                $ParasitePDO2->query($selectForUpdate1);
            }
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $isParasitePDOException = $e instanceof LockWaitTimeoutException;
            $this->assertInstanceOf('PDOException', $e);
        }
        $ParasitePDO1->rollBack();
        
        $this->assertTrue($exceptionCaught);
        
        $this->assertSame(
            $addRethrowLockWaitTimeout,
            $isParasitePDOException,
            $e
        );
    }
    
    /**
     * @return \ParasitePDO\hosts\ParasitePDO
     */
    
    private function returnInjectedParasitePDO()
    {
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        return new ParasitePDO($PDO);
    }
    
    /**
     * @return \ParasitePDO\hosts\ParasitePDO
     */
    
    private function returnConstructedParasitePDO()
    {
        return new ParasitePDO($this->dsn,$this->username,$this->password);
    }
    
    private function setupDatabaseAndTable()
    {
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $PDO->query("CREATE DATABASE IF NOT EXISTS $this->dbname")->execute();
        $PDO->query("USE $this->dbname")->execute();
        $PDO->query("DROP TABLE IF EXISTS $this->tablename")->execute();
        $PDO->query("CREATE TABLE $this->tablename (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB");
    }
}

