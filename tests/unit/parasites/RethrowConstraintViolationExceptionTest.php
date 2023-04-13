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
    
    public function testRunThrowsSetterRequiredIfNotSetFormatExceptionMessage()
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
     * @group setErrorInfo()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setErrorInfo()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetErrorInfo()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setErrorInfo']
        );
        
        $this->expectException('PhpRun\CodeStyle\Exceptions\SetterRequiredException');
        
        $SUT->run();
    }
    
    /**
     * @group setDriverName()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setDriverName()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetDriverName()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setDriverName']
        );
        
        $this->expectException('PhpRun\CodeStyle\Exceptions\SetterRequiredException');
        
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
        
        $errorInfo = [
            $code,
            1234,
            uniqid()
        ];
        $driverName = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        $this->expectException('ParasitePDO\exceptions\ConstraintViolationException');
        
        $SUT->run();
    }
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @group run()
     * 
     * @testdox run() formats an exception message using the previous exception and the query string if the exception is thrown; Also the boundInputParams are passed, if set
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
        
        $errorInfo = [
            '23000',
            9999,
            uniqid()
        ];
        $driverName = uniqid();
        
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
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
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
     * @testdox run() sets PDOException to the ConstraintViolationException's previous exception if the exception is thrown
     */
    
    public function testConstraintViolationSetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            uniqid(),
            23000,
            null
        );
        $statement = uniqid();
        
        $errorInfo = [
            '23000',
            9999,
            uniqid()
        ];
        $driverName = uniqid();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
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
     * @testdox run() throws DuplicateKeyException instead of ConstraintViolationException if the driverName == 'mysql' and errorInfo has the code 1062, which is MySQL's duplicate key error code
     */
    
    public function testIfPrevExceptionHasMysqlDupKeyThenThrowsDupKey()
    {
        $PDOException = new \PDOException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'",
            23000,
            null
        );
        $statement = uniqid();
        
        $errorInfo = [
            '23000',
            1062,
            "Duplicate entry '1' for key 'PRIMARY'"
        ];
        $driverName = 'mysql';
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() sets PDOException to the DuplicateKeyException previous exception if the exception is thrown
     */
    
    public function testDupKeySetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'",
            23000,
            null
        );
        $statement = uniqid();
        
        $errorInfo = [
            '23000',
            1062,
            "Duplicate entry '1' for key 'PRIMARY'"
        ];
        $driverName = 'mysql';
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
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
    
    /**
     * @doesNotPerformAssertions
     * 
     * @group run()
     * 
     * @testdox run() does nothing if the exception code does not start with '23' and the errorInfo does not include error code 1062 for a duplicate key exception
     */
    
    public function testDoesNothingIfExceptionIsNotConstraintViolation()
    {
        $PDOException = new \PDOException(
            "PDOException: SQLSTATE[HY000]: ".uniqid(),
            00000,
            null
        );
        $statement = uniqid();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $errorInfo = [
            'HY000',
            9999,
            null
        ];
        $driverName = 'mysql';
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        $SUT->run();
    }
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @group run()
     * 
     * @testdox run() throws ConstraintViolationException instead of DuplicateKeyException if the driverName != 'mysql', even if the errorInfo code is 1062
     */
    
    public function testStaysWithConstraintExceptionIfDriverNotMysql(
        $driverIsMysql
    )
    {
        $PDOException = new \PDOException(
            "PDOException: SQLSTATE[HY000]: ".uniqid(),
            '23000',
            null
        );
        $statement = uniqid();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $errorInfo = [
            '23000',
            1062,
            "Duplicate entry '1' for key 'PRIMARY'"
        ];
        if ($driverIsMysql) {
            $driverName = 'mysql';
        } else {
            $driverName = uniqid();
        }
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        if ($driverIsMysql) {
            $this->expectException('ParasitePDO\exceptions\DuplicateKeyException');
        } else {
            $this->expectException('ParasitePDO\exceptions\ConstraintViolationException');
        }
        
        $SUT->run();
    }
    
    private function returnSubjectUnderTest()
    {
        return new RethrowConstraintViolationException();
    }
    
    private function returnFormatExceptionMessageMock()
    {
        return $this->getMockBuilder(
            'ParasitePDO\formatters\IFormatExceptionMessage'
        )->getMock();
    }
    
    
    private function setRequiredSettersExceptAsSpecified(
        $SUT,
        array $specified
    )
    {
        if (!in_array($method='setPDOException',$specified)) {
            $SUT->$method(new \PDOException());
        }
        if (!in_array($method='setStatement',$specified)) {
            $SUT->$method(uniqid());
        }
        if (!in_array($method='setFormatExceptionMessage',$specified)) {
            $SUT->$method($this->returnFormatExceptionMessageMock());
        }
        if (!in_array($method='setErrorInfo',$specified)) {
            $SUT->$method($this->returnErrorInfoNoErrorStub());
        }
        if (!in_array($method='setDriverName',$specified)) {
            $SUT->$method(uniqid());
        }
    }
    
    private function returnErrorInfoNoErrorStub()
    {
        return [
            '00000',
            null,
            null
        ];
    }
}

