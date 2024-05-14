<?php
require_once __DIR__.'/../../TestHelpers.php';

use PHPUnit\Framework\TestCase;
use ParasitePDO\parasites\RethrowExceptionWithQueryInfo;

class RethrowExceptionWithQueryInfoTest extends TestCase
{
    use \TestHelpers;
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
        
        $this->expectException('PhpRun\CodeStyle\Exceptions\SetterRequiredException');
        
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
        
        $this->expectException('PhpRun\CodeStyle\Exceptions\SetterRequiredException');
        
        $SUT->run();
    }
    
    /**
     * @group setFormatExceptionMessage()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setFormatExceptionMessage()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetFormatMessage()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setFormatExceptionMessage']
        );
        
        $this->expectException('PhpRun\CodeStyle\Exceptions\SetterRequiredException');
        
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
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->any())
            ->method('getFormattedExceptionMessage')
            ->will($this->returnValue(''));
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        
        $this->expectException('ParasitePDO\hosts\ParasitePDOException');
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() sets the setPDOException()'s arg to the ParasitePDOException's previous exception if the exception is thrown
     */
    
    public function testRunSetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            uniqid(),
            mt_rand(1,100000),
            null
        );
        $statement = uniqid();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->any())
            ->method('getFormattedExceptionMessage')
            ->will($this->returnValue(''));
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        
        try {
            $SUT->run();
        } catch (\Exception $e) {
            $this->assertSame(
                $PDOException,
                $e->getPrevious()
            );
        }
    }
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @group run()
     * 
     * @testdox run() formats an exception message using the previous exception message and the query string if the exception is thrown; Also the boundInputParams are passed, if set
     */
    
    public function testIfExceptionThrownThenMessageIsFormatted(
        $setBoundInputParams
    )
    {
        $PDOException = new \PDOException(
           $previousExceptionMessage = uniqid(),
            mt_rand(1,100000),
            null
        );
        $statement = uniqid();
        $boundInputParams = [uniqid()=>uniqid()];
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->once())
            ->method('setPreviousExceptionMessage')
            ->with($this->equalTo($previousExceptionMessage));
        $FormatExceptionMessage
            ->expects($this->once())
            ->method('setQueryString')
            ->with($this->equalTo($statement));
        if ($setBoundInputParams) {
            $FormatExceptionMessage
                ->expects($this->once())
                ->method('setBoundInputParams')
                ->with($this->equalTo($boundInputParams));
        }
        $FormatExceptionMessage
            ->expects($this->once())
            ->method('run');
        $FormatExceptionMessage
            ->expects($this->once())
            ->method('getFormattedExceptionMessage')
            ->will($this->returnValue($formattedExceptionMessage=uniqid()));
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        if ($setBoundInputParams) {
            $SUT->setBoundInputParams($boundInputParams);
        }
        
        $this->expectException('ParasitePDO\hosts\ParasitePDOException');
        $this->expectExceptionMessage($formattedExceptionMessage);
        
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
        if (!in_array('setFormatExceptionMessage',$specified)) {
            $SUT->setFormatExceptionMessage($this->returnFormatExceptionMessageMock());
        }
    }
    
    private function returnFormatExceptionMessageMock()
    {
        return $this->getMockBuilder(
            'ParasitePDO\formatters\IFormatExceptionMessage'
        )->getMock();
    }
}

