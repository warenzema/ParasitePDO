<?php
namespace ParasitePDO\unit\parasites;

require_once __DIR__.'/../../TestHelpers.php';

use PHPUnit\Framework\TestCase;
use ParasitePDO\parasites\RethrowConstraintViolationException;

class RethrowConstraintViolationExceptionTest extends TestCase
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
     * @group setFormatExceptionMessage()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setFormatExceptionMessage()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetFormatExceptionMessage()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setFormatExceptionMessage']
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
     * 
     * @group run()
     * 
     * @testdox run() throws ConstraintViolationException if 23000 <= $code < 24000, where $code is the 2nd argument of the setPDOException() arg's constructor
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
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        
        $this->expectException('ParasitePDO\exceptions\ConstraintViolationException');
        
        $SUT->run();
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
            23000,
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
        
        $this->expectException('ParasitePDO\exceptions\ConstraintViolationException');
        $this->expectExceptionMessage($formattedExceptionMessage);
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() sets the setPDOException()'s arg to the ConstraintViolationException's previous exception if the exception is thrown
     */
    
    public function testConstraintViolationSetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            uniqid(),
            23000,
            null
        );
        $statement = uniqid();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
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
     * @group run()
     * 
     * @testdox run() throws DuplicateKeyException instead of ConstraintViolationException if the setPDOException()'s arg's message contains text indicating there is a duplicate entry, using MySQL's syntax
     */
    
    public function testIfPrevExceptionHasMysqlDupKeyThenThrowsDupKey()
    {
        $PDOException = new \PDOException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'",
            23000,
            null
        );
        $statement = uniqid();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() sets the setPDOException()'s arg to the DuplicateKeyException previous exception if the exception is thrown
     */
    
    public function testDupKeySetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'",
            23000,
            null
        );
        $statement = uniqid();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
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
            $this->assertInstanceOf(
                'ParasitePDO\exceptions\DuplicateKeyException',
                $e
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

