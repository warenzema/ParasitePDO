<?php
use PHPUnit\Framework\TestCase;
use ParasitePDO\parasites\RethrowExceptionWithQueryInfo;

class RethrowExceptionWithQueryInfoTest extends TestCase
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
    
    /**
     * @group run()
     * 
     * @testdox run() throws ParasitePDOException for all exceptions, regardless of code or message
     */
    
    public function testRunRethrowsExceptionRegardlessOfCode()
    {
        $PDOException = new \PDOException(
            uniqid(),
            mt_rand(1,100000),
            null
        );
        $statement = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        
        $this->expectException('ParasitePDO\hosts\ParasitePDOException');
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() adds the setStatement()'s arg to the ConstraintViolationException's message if the exception is thrown
     */
    
    public function testIfExceptionThrownThenMessageIsStatement()
    {
        $PDOException = new \PDOException(
            uniqid(),
            mt_rand(1,100000),
            null
        );
        $statement = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        
        $this->expectException('ParasitePDO\hosts\ParasitePDOException');
        $this->expectExceptionMessage($statement);
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() sets the setPDOException()'s arg to the ConstraintViolationException's previous exception if the exception is thrown
     */
    
    public function testRunSetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            uniqid(),
            mt_rand(1,100000),
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
    
    public function providerSetBoundInputParamsAndArg()
    {
        return [
            [true,null],
            [true,[]],
            [true,''],
            [false,null],
        ];
    }
    
    /**
     * @dataProvider providerSetBoundInputParamsAndArg
     * 
     * @group run()
     * @group setBoundInputParams()
     * 
     * @testdox run() adds "No params were bound" to the ParasitePDOException's message if setBoundInputParams() is either not set or it's arg is empty
     */
    
    public function testNoBoundParamsStatesAsSuch(
        $setBoundInputParams,
        $boundInputParams
    )
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
        if ($setBoundInputParams) {
            $SUT->setBoundInputParams($boundInputParams);
        }
        
        $expectedExceptionMessage = "$statement\n\nNo params were bound.";
        
        $this->expectException('ParasitePDO\hosts\ParasitePDOException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * @group setBoundInputParams()
     * 
     * @testdox run() adds key value pairs from setBoundInputParams()'s arg to ParasitePDOException's message if arg is associative array
     */
    
    public function testBoundParamsSetAddsThoseParamsToMessage()
    {
        $PDOException = new \PDOException(
            uniqid(),
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
        
        $this->expectException('ParasitePDO\hosts\ParasitePDOException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        
        $SUT->run();
    }
    
    private function returnSubjectUnderTest()
    {
        return new RethrowExceptionWithQueryInfo();
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

