<?php

require_once __DIR__.'/../TestHelpers.php';

use PHPUnit\Framework\TestCase;
use ParasitePDO\hosts\ParasitePDO;
use ParasitePDO\exceptions\DuplicateKeyException;
use ParasitePDO\hosts\ParasitePDOException;
use ParasitePDO\parasites\RethrowConstraintViolationExceptionFactory;
use ParasitePDO\parasites\RethrowExceptionWithQueryInfoFactory;

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
     * @testdox if RethrowConstraintVioldationException is added to ParasitePDO, then DuplicateKeyException is thrown when using ParasitePDO::exec() with a statement that causes duplicate key exception; else the normal PDOException is thrown if no rethrows are added
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
     * @dataProvider providerTrueFalse1
     * 
     * @testdox if RethrowConstraintVioldationException is added to ParasitePDO, then DuplicateKeyException is thrown when using ParasitePDO::prepare() and then ParasitePDOStatement::execute() with a statement that causes duplicate key exception; else the normal PDOException is thrown is no rethrows are added
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
        $ParasitePDO->addRethrowException(
            (new RethrowConstraintViolationExceptionFactory())
            ->build()
        );
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
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @testdox if RethrowExceptionWithQueryInfo is added to ParasitePDO, then ParasitePDOException is thrown when using ParasitePDO::query() with a statement that causes exception; else the normal PDOException is thrown if no rethrows are added
     */
    
    public function testRethrowWithQueryInfoWorksWithQuery(
        $addRethrowWithQueryInfo
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
        $query = "INSERT INTO $tablename (`no_such_column`) VALUES (1)";
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
     * @dataProvider providerTrueFalse1
     * 
     * @testdox if RethrowExceptionWithQueryInfo is added to ParasitePDO, then ParasitePDOException is thrown when using ParasitePDO::prepare() and then ParasitePDOStatement::execute(); else the normal PDOException is thrown if no rethrows are added
     */
    
    public function testRethrowWithQueryInfoWorksWithPrepare(
        $addRethrowWithQueryInfo
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
        $query = "INSERT INTO $tablename (`no_such_column`) VALUES (1)";
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
}

