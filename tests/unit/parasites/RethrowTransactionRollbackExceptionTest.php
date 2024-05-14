<?php
require_once __DIR__.'/../../TestHelpers.php';

use PHPUnit\Framework\TestCase;
use ParasitePDO\parasites\RethrowTransactionRollbackException;

class RethrowTransactionRollbackExceptionTest extends TestCase
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
     * @group setParasitePDO()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setParasitePDO()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetParasitePDO()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setParasitePDO']
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
    
    public function provider40000To40999()
    {
        return [
            [40000],
            [40001],
            [40100],
            [40500],
            [40999],
        ];
    }
    
    /**
     * @dataProvider provider40000To40999
     * 
     * @group run()
     * 
     * @testdox run() throws TransactionRollbackException if 40000 <= $code < 41000, where $code is the 2nd argument of the setPDOException() arg's constructor
     */
    
    public function testRunThrowsTransactionRollbackExIfIn40000CodeRange(
        $code   
    )
    {
        $PDOException = new \PDOException(
            uniqid(),
            $code,
            null
        );
        $statement = uniqid();
        
        $ParasitePDO = $this->returnParasitePDOStub();
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->any())
            ->method('getFormattedExceptionMessage')
            ->will($this->returnValue(''));
        
        $errorInfo = [
            $code,
            1234,
            uniqid()
        ];
        $driverName = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        $this->expectException('ParasitePDO\exceptions\TransactionRollbackException');
        
        $SUT->run();
    }
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @group run()
     * 
     * @testdox run() formats an exception message using the previous exception message and the query string if the exception is thrown; Also the boundInputParams are passed, if set; Also, if driverName == 'mysql', the database is queried with `SHOW ENGINE INNODB STATUS` and `SHOW FULL PROCESSLIST`, and their respective outputs added as additional information
     */
    
    public function testIfExceptionThrownThenMessageIsFormatted(
        $setBoundInputParams
    )
    {
        $PDOException = new \PDOException(
           $previousExceptionMessage = uniqid(),
            40001,
            null
        );
        $statement = uniqid();
        $boundInputParams = [uniqid()=>uniqid()];
        $ParasitePDO = $this->returnParasitePDOMock();
        
        $errorInfo = [
            '40001',
            1213,
            uniqid()
        ];
        $driverName = 'mysql';
        
        $innoDbStatus = [
            'Type'=>uniqid(),
            'Name'=>uniqid(),
            'Status'=>$status=uniqid(),
        ];
        $ParasitePDOStatement1 = $this->returnParasitePDOStatementMock();
        $ParasitePDOStatement1
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo(PDO::FETCH_ASSOC))
            ->will($this->returnValue($innoDbStatus));
        
        $fullProcesslist = [
            [
                uniqid()=>uniqid(),
                uniqid()=>uniqid(),
            ],
            [
                uniqid()=>uniqid(),
                uniqid()=>uniqid(),
            ],
        ];
        $ParasitePDOStatement2 = $this->returnParasitePDOStatementMock();
        $ParasitePDOStatement2
            ->expects($this->once())
            ->method('fetchAll')
            ->with($this->equalTo(PDO::FETCH_ASSOC))
            ->will($this->returnValue($fullProcesslist));

        $ParasitePDO
            ->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(
                [$this->equalTo('SHOW ENGINE INNODB STATUS')],
                [$this->equalTo('SHOW FULL PROCESSLIST')]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnValue($ParasitePDOStatement1),
                $this->returnValue($ParasitePDOStatement2)
            );
        
        $additionalMessage = "InnoDb Status:\n\n$status"
            ."\n\nFull Processlist:\n\n"
            .print_r($fullProcesslist,true);
        
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
            ->method('setAdditionalMessage')
            ->with($this->equalTo($additionalMessage));
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
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        if ($setBoundInputParams) {
            $SUT->setBoundInputParams($boundInputParams);
        }
        
        $this->expectException('ParasitePDO\exceptions\TransactionRollbackException');
        $this->expectExceptionMessage($formattedExceptionMessage);
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() does not set additional information to the message if driverName != 'mysql'
     */
    
    public function testIfExceptionButNotMysqlThenNotQueryAdditional()
    {
        $PDOException = new \PDOException(
           $previousExceptionMessage = uniqid(),
            40001,
            null
        );
        $statement = uniqid();
        $ParasitePDO = $this->returnParasitePDOMock();
        
        $errorInfo = [
            '40001',
            1234,
            uniqid()
        ];
        $driverName = uniqid();
        
        $ParasitePDO
            ->expects($this->never())
            ->method('query');
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->once())
            ->method('setPreviousExceptionMessage')
            ->with($this->equalTo($previousExceptionMessage));
        $FormatExceptionMessage
            ->expects($this->once())
            ->method('setQueryString')
            ->with($this->equalTo($statement));
        $FormatExceptionMessage
            ->expects($this->never())
            ->method('setAdditionalMessage');
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
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        $this->expectException('ParasitePDO\exceptions\TransactionRollbackException');
        $this->expectExceptionMessage($formattedExceptionMessage);
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() sets the PDOException to the TransactionRollbackException's previous exception if the exception is thrown
     */
    
    public function testThrownExceptionSetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            uniqid(),
            40001,
            null
        );
        $statement = uniqid();
        
        $ParasitePDO = $this->returnParasitePDOStub();
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->any())
            ->method('getFormattedExceptionMessage')
            ->will($this->returnValue(''));
        
        $errorInfo = [
            '40001',
            1234,
            uniqid()
        ];
        $driverName = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        $exceptionCaught = false;
        try {
            $SUT->run();
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $this->assertSame(
                $PDOException,
                $e->getPrevious()
            );
            $this->assertInstanceOf(
                'ParasitePDO\exceptions\TransactionRollbackException',
                $e
            );
        }
        $this->assertTrue($exceptionCaught);
    }
    
    /**
     * @group run()
     * 
     * @testdox run() throws DeadlockException instead of TransactionRollbackException if the driverName == 'mysql' and the errorInfo array contains code 1213, which is MySQL's specific code for deadlocks
     */
    
    public function testIfPrevExceptionHasMysqlDeadlockThenThrowsDeadlock()
    {
        $PDOException = new \PDOException(
            uniqid(),
            40001,
            null
        );
        $statement = uniqid();
        $ParasitePDO = $this->returnParasitePDOStub();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->any())
            ->method('getFormattedExceptionMessage')
            ->will($this->returnValue(''));
        
        $errorInfo = [
            '40001',
            1213,
            'Deadlock found when trying to get lock; try restarting transaction'
        ];
        $driverName = 'mysql';
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        $this->expectException('ParasitePDO\exceptions\DeadlockException');
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() sets the PDOException to the DeadlockException previous exception if the exception is thrown
     */
    
    public function testDeadlockSetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            uniqid(),
            40001,
            null
        );
        $statement = uniqid();
        $ParasitePDO = $this->returnParasitePDOStub();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->any())
            ->method('getFormattedExceptionMessage')
            ->will($this->returnValue(''));
        
        $errorInfo = [
            '40001',
            1213,
            'Deadlock found when trying to get lock; try restarting transaction'
        ];
        $driverName = 'mysql';
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        $exceptionCaught = false;
        try {
            $SUT->run();
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $this->assertSame(
                $PDOException,
                $e->getPrevious()
            );
            $this->assertInstanceOf(
                'ParasitePDO\exceptions\DeadlockException',
                $e
            );
        }
        $this->assertTrue($exceptionCaught);
    }
    
    /**
     * @doesNotPerformAssertions
     * 
     * @group run()
     * 
     * @testdox run() does nothing if the exception code does not start with '40' and the errorInfo does not include error code 1213 for a deadlock exception
     */
    
    public function testDoesNothingIfExceptionIsNotTransactionRollback()
    {
        $PDOException = new \PDOException(
            "PDOException: SQLSTATE[HY000]: ".uniqid(),
            00000,
            null
        );
        $statement = uniqid();
        $ParasitePDO = $this->returnParasitePDOStub();
        
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
        $SUT->setParasitePDO($ParasitePDO);
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
     * @testdox run() throws TransactionRollbackException instead of DeadlockException if the driverName != 'mysql', even if the errorInfo code is 1213
     */
    
    public function testStaysWithRollbackExceptionIfDriverNotMysql(
        $driverIsMysql
    )
    {
        $PDOException = new \PDOException(
            "PDOException: SQLSTATE[HY000]: ".uniqid(),
            '40001',
            null
        );
        $statement = uniqid();
        $ParasitePDO = $this->returnParasitePDOStub();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        $FormatExceptionMessage
            ->expects($this->any())
            ->method('getFormattedExceptionMessage')
            ->will($this->returnValue(''));
        
        $errorInfo = [
            '40001',
            1213,
            'Deadlock found when trying to get lock; try restarting transaction'
        ];
        if ($driverIsMysql) {
            $driverName = 'mysql';
        } else {
            $driverName = uniqid();
        }
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        $SUT->setErrorInfo($errorInfo);
        $SUT->setDriverName($driverName);
        
        if ($driverIsMysql) {
            $this->expectException('ParasitePDO\exceptions\DeadlockException');
        } else {
            $this->expectException('ParasitePDO\exceptions\TransactionRollbackException');
        }

        $SUT->run();
    }
    
    private function returnSubjectUnderTest()
    {
        return new RethrowTransactionRollbackException();
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
        if (!in_array($method='setParasitePDO',$specified)) {
            $SUT->$method($this->returnParasitePDOMock());
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
    
    private function returnFormatExceptionMessageMock()
    {
        return $this->getMockBuilder(
            'ParasitePDO\formatters\IFormatExceptionMessage'
        )->getMock();
    }
    
    private function returnParasitePDOMock()
    {
        return $this->getMockBuilder(
            'ParasitePDO\hosts\ParasitePDO'
        )
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    private function returnParasitePDOStatementMock()
    {
        return $this->getMockBuilder(
            'ParasitePDO\hosts\ParasitePDOStatement'
        )
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    private function returnParasitePDOStub()
    {
        $ParasitePDO = $this->returnParasitePDOMock();
        $ParasitePDO
            ->expects($this->any())
            ->method('query')
            ->will($this->returnValue($this->returnParasitePDOStatementStub()));
        
        return $ParasitePDO;
    }
    
    private function returnParasitePDOStatementStub()
    {
        $ParasitePDOStatement = $this->returnParasitePDOStatementMock();
        
        $ParasitePDOStatement
            ->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue([]));
        $ParasitePDOStatement
            ->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(['Status'=>uniqid()]));
        
        return $ParasitePDOStatement;
    }
}

