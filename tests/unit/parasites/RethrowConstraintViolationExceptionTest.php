<?php
namespace ParasitePDO\unit\parasites;

use PHPUnit\Framework\TestCase;
use ParasitePDO\parasites\RethrowConstraintViolationException;

class RethrowConstraintViolationExceptionTest extends TestCase
{
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
    
    public function testNoBoundParamsStatesAsSuch()
    {
        $PDOException = new \PDOException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'",
            23000,
            null
        );
        $statement = uniqid();
        $boundInputParams = [];
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setBoundInputParams($boundInputParams);
        
        $expectedExceptionMessage = "$statement\n\nNo params were bound.";
        
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        
        $SUT->run();
    }
    
    public function testBoundParamsSetAddsThoseParamsToMessage()
    {
        $PDOException = new \PDOException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'",
            23000,
            null
        );
        $statement = uniqid();
        $boundInputParams = [
            $key1=uniqid()=>$value1=uniqid(),
            $key2=uniqid()=>$value2=uniqid(),
        ];
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setBoundInputParams($boundInputParams);
        
        $expectedExceptionMessage = "$statement\n\nBound with: '$key1'=>'$value1', '$key2'=>'$value2'";
        
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        
        $SUT->run();
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

