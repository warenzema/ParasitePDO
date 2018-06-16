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
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        
        $this->expectException('ParasitePDO\exceptions\TransactionRollbackException');
        
        $SUT->run();
    }
    
    /**
     * @dataProvider providerTrueFalse1
     * 
     * @group run()
     * 
     * @testdox run() formats an exception message using the previous exception message and the query string if the exception is thrown; Also the boundInputParams are passed, if set; Also, the database is queried with `SHOW ENGINE INNODB STATUS` and `SHOW FULL PROCESSLIST`, and their respective outputs added as additional information
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
        
        $parasitePdoAt = 0;
        $ParasitePDO
            ->expects($this->at($parasitePdoAt++))
            ->method('query')
            ->with($this->equalTo('SHOW ENGINE INNODB STATUS'))
            ->will($this->returnValue($ParasitePDOStatement1));
        $ParasitePDO
            ->expects($this->at($parasitePdoAt++))
            ->method('query')
            ->with($this->equalTo('SHOW FULL PROCESSLIST'))
            ->will($this->returnValue($ParasitePDOStatement2));
        
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
     * @testdox run() sets the setPDOException()'s arg to the TransactionRollbackException's previous exception if the exception is thrown
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
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        
        $exceptionCaught = false;
        try {
            $SUT->run();
        } catch (\Exception $e) {
            $exceptionCaught = true;
            $this->assertSame(
                $PDOException,
                $e->getPrevious()
            );
        }
        $this->assertTrue($exceptionCaught);
    }
    
    /**
     * @group run()
     * 
     * @testdox run() throws DeadlockException instead of TransactionRollbackException if the setPDOException()'s arg's message contains text indicating there is a deadlock, using MySQL's syntax
     */
    
    public function testIfPrevExceptionHasMysqlDupKeyThenThrowsDupKey()
    {
        $PDOException = new \PDOException(
            "PDOException: SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction",
            40001,
            null
        );
        $statement = uniqid();
        $ParasitePDO = $this->returnParasitePDOStub();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        
        $this->expectException('ParasitePDO\exceptions\DeadlockException');
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * 
     * @testdox run() sets the setPDOException()'s arg to the DeadlockException previous exception if the exception is thrown
     */
    
    public function testDeadlockSetsPrevExceptionAsSetPDOException()
    {
        $PDOException = new \PDOException(
            "PDOException: SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction",
            40001,
            null
        );
        $statement = uniqid();
        $ParasitePDO = $this->returnParasitePDOStub();
        
        $FormatExceptionMessage = $this->returnFormatExceptionMessageMock();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPDOException($PDOException);
        $SUT->setStatement($statement);
        $SUT->setParasitePDO($ParasitePDO);
        $SUT->setFormatExceptionMessage($FormatExceptionMessage);
        
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
    
    private function returnSubjectUnderTest()
    {
        return new RethrowTransactionRollbackException();
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
        if (!in_array('setParasitePDO',$specified)) {
            $SUT->setParasitePDO($this->returnParasitePDOMock());
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

