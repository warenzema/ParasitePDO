<?php
namespace ParasitePDO\hosts;

require_once __DIR__.'/../../TestHelpers.php';

use PHPUnit\Framework\TestCase;

class ParasitePDOTest extends TestCase
{
    use \TestHelpers;
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
     * @testdox ParasitePDO can instantiate the same way as a \PDO object instantiates, and is an instance of a \PDO object
     */
    
    public function testInstantiate($className)
    {
        $object = new $className($this->dsn,$this->username,$this->password);
        
        $this->assertInstanceOf($className, $object);
        
        $this->assertInstanceOf('\PDO',$object);
    }
    
    /**
     * @testdox ParasitePDO can accept a \PDO object during construction instead of the normal arguments \PDO::__construct() uses
     */
    
    public function testCanInject()
    {
        $PDO = new \PDO($this->dsn,$this->username,$this->password);
        
        $ParasitePDO = new ParasitePDO($PDO);
        
        $this->assertInstanceOf('\PDO',$ParasitePDO);
    }
    
    /**
     * @dataProvider providerTrueFalse2
     * 
     * @group query()
     * @group prepare()
     * 
     * @testdox query() and prepare() return ParasitePDOStatement objects upon success
     */
    
    public function testReturnedQueryStatementIsParasiteStatement(
        $injectPDOInsteadOfConstruct,
        $prepareInsteadOfQuery
    )
    {
        $dbname = 'db'.uniqid();
        
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        
        if ($prepareInsteadOfQuery) {
            $statement = $ParasitePDO
                ->prepare("CREATE DATABASE IF NOT EXISTS $dbname");
        } else {
            $statement = $ParasitePDO
                ->query("CREATE DATABASE IF NOT EXISTS $dbname");
        }
        
        $this->assertInstanceOf(
            'ParasitePDO\hosts\ParasitePDOStatement',
            $statement
        );
    }
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @group query()
     * 
     * @testdox query()===false if arg is invalid SQL statement
     */
    
    public function testReturnedQueryInvalidStatementIsFalse(
        $injectPDOInsteadOfConstruct
    )
    {
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        
        $statement = $ParasitePDO->query("invalid statement");
        
        $this->assertFalse($statement);
    }
    
    /**
     * @group prepare()
     * 
     * @testdox prepare()===false if unable to prepare statement; This is untested as a way to force \PDO::prepare() to return false was not found.
     */
    
    public function testReturnedPrepareInvalidStatementIsFalse()
    {
        $this->markTestSkipped('Could not determine way to force PDO::prepare() to return false');
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
    
    public function providerParentObjectPublicMethodsTrueFalse1()
    {
        return $this->mergeDataProviders(
            $this->providerParentObjectPublicMethods(),
            $this->providerTrueFalse1()
        );
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
        'prepare'=>['blah blah'],
        'query'=>['blah blah'],
        'quote'=>[],
        'rollBack'=>[],
        'setAttribute'=>[\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION],
    ];
    
    /**
     * @dataProvider providerParentObjectPublicMethodsTrueFalse1
     */
    
    public function testAllPublicMethodsAreOverwritten(
        $method,
        $injectPDOInsteadOfConstruct
    )
    {
        $this->assertArrayHasKey($method, $this->publicMethodArgs);
        $args = $this->publicMethodArgs[$method];
        
        if ($injectPDOInsteadOfConstruct) {
            $ParasitePDO = $this->returnInjectedParasitePDO();
        } else {
            $ParasitePDO = $this->returnConstructedParasitePDO();
        }
        
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

