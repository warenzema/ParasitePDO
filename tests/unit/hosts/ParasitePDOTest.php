<?php
namespace ParasitePDO\hosts;

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
            ['ParasitePDO\hosts\ParasitePDO']
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
        
        $this->assertInstanceOf('ParasitePDO\hosts\ParasitePDOStatement', $statement);
    }
    
    public function testInvalidStatementIsFalse()
    {
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $ParasitePDO = new ParasitePDO($PDO);
        
        $statement = $ParasitePDO->query("invalid statement");
        
        $this->assertFalse($statement);
    }
    
    public function providerParentObjectPublicMethods()
    {
        $Reflection = new \ReflectionClass('\PDO');
        $PublicMethods = $Reflection->getMethods(
            \ReflectionMethod::IS_PUBLIC
        );
        
        $StaticMethods = $Reflection->getMethods(
            \ReflectionMethod::IS_STATIC
        );
        
        $provider = [];
        foreach ($PublicMethods as $ReflectionMethod) {
            $publicMethodName = $ReflectionMethod->name;
            //skip __construct, __sleep, and __wakeup
            if (0 === strpos($publicMethodName, '__')) {
                continue;
            }
            //skip public static methods
            foreach ($StaticMethods as $StaticMethod) {
                $staticMethodName = $StaticMethod->name;
                if ($staticMethodName == $publicMethodName) {
                    continue 2;
                }
            }
            $provider[] = [$publicMethodName];
        }
        
        return $provider;
    }
    
    private $publicMethodArgs = [
        'beginTransaction'=>[],
        'commit'=>[],
        'errorCode'=>[],
        'errorInfo'=>[],
        'exec'=>['blah blah'],
        'getAttribute'=>[\PDO::ATTR_ERRMODE],
        'inTransaction'=>[],
        'lastInsertId'=>[],
        'prepare'=>['blah blah'],//TODO
        'query'=>['blah blah'],
        'quote'=>[],
        'rollBack'=>[],
        'setAttribute'=>[\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION],
    ];
    
    /**
     * @dataProvider providerParentObjectPublicMethods
     */
    
    public function testAllPublicMethodsAreOverwritten(
        $method
    )
    {
        $this->assertArrayHasKey($method, $this->publicMethodArgs);
        $args = $this->publicMethodArgs[$method];
        
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $ParasitePDO = new ParasitePDO($PDO);
        try {
            $ParasitePDO->$method(...$args);
        } catch (\Exception $e) {
            //we don't care about the error, so long as it isn't the
            //one associated with a method not being set
            $this->assertFalse(
                strpos($e->getMessage(), 'PDO constructor was not called'),
                "Method '$method' is not overwritten in ParasitePDO and will not function properly\n"
            );
        }
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

