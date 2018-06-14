<?php
namespace ParasitePDO\formatters;

use PHPUnit\Framework\TestCase;

class FormatExceptionMessageWithMaximumInfoTest extends TestCase
{
    /**
     * @group setPreviousExceptionMessage()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setPreviousExceptionMessage()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetPreviousMessage()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setPreviousExceptionMessage']
        );
        
        $this->expectException('ParasitePDO\exceptions\SetterRequiredException');
        
        $SUT->run();
    }
    
    /**
     * @group setQueryString()
     * @group run()
     * 
     * @testdox run() throws SetterRequiredException if not setQueryString()
     */
    
    public function testRunThrowsSetterRequiredIfNotSetQueryString()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->setRequiredSettersExceptAsSpecified(
            $SUT,
            ['setQueryString']
        );
        
        $this->expectException('ParasitePDO\exceptions\SetterRequiredException');
        
        $SUT->run();
    }
    
    /**
     * @group run()
     * @group getFormattedExceptionMessage()
     * 
     * @testdox getFormattedExceptionMessage() throws ObjectNotRunYetException if run() not called first
     */
    
    public function testGetMessageThrowsObjectNotRunYetIfRunNotCalled()
    {
        $SUT = $this->returnSubjectUnderTest();
        
        $this->expectException('ParasitePDO\exceptions\ObjectNotRunYetException');
        
        $SUT->getFormattedExceptionMessage();
    }
    
    /**
     * @group getFormattedExceptionMessage()
     * @group setPreviousExceptionMessage()
     * @group setQueryString()
     * 
     * @testdox getFormattedExceptionMessage() return value always includes setPreviousExceptionMessage() and setQueryString()'s args
     */
    
    public function testAllMessagesIncludePreviousMessageAndQueryString()
    {
        $previousExceptionMessage = uniqid();
        $queryString = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPreviousExceptionMessage($previousExceptionMessage);
        $SUT->setQueryString($queryString);
        
        $expectedExceptionMessageStart = $previousExceptionMessage
            ."\n\nCaused by query:\n"
            .$queryString;
        
        $SUT->run();
        
        $this->assertStringStartsWith(
            $expectedExceptionMessageStart,
            $SUT->getFormattedExceptionMessage()
        );
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
     * @testdox getFormattedExceptionMessage() return value includes "No params were bound" if setBoundInputParams() is either not set or it's arg is empty
     */
    
    public function testNoBoundParamsStatesAsSuch(
        $setBoundInputParams,
        $boundInputParams
    )
    {
        $previousExceptionMessage = uniqid();
        $queryString = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPreviousExceptionMessage($previousExceptionMessage);
        $SUT->setQueryString($queryString);
        if ($setBoundInputParams) {
            $SUT->setBoundInputParams($boundInputParams);
        }
        
        $expectedExceptionMessage = "No params were bound.";
        
        $SUT->run();
        
        $this->assertStringEndsWith(
            $expectedExceptionMessage,
            $SUT->getFormattedExceptionMessage()
        );
    }
    
    /**
     * @group run()
     * @group setBoundInputParams()
     * 
     * @testdox getFormattedExceptionMessage() return value includes key value pairs from setBoundInputParams()'s arg if arg is associative array
     */
    
    public function testBoundParamsSetAddsThoseParamsToMessage()
    {
        $previousExceptionMessage = uniqid();
        $queryString = uniqid();
        
        $boundInputParams = [
            $key1=uniqid()=>$value1=uniqid(),
            $key2=uniqid()=>$value2=uniqid(),
        ];
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPreviousExceptionMessage($previousExceptionMessage);
        $SUT->setQueryString($queryString);
        $SUT->setBoundInputParams($boundInputParams);
        
        $expectedExceptionMessage = "Bound with: '$key1'=>'$value1', '$key2'=>'$value2'";
        
        $SUT->run();
        
        $this->assertStringEndsWith(
            $expectedExceptionMessage,
            $SUT->getFormattedExceptionMessage()
        );
    }
    
    /**
     * @group getFormattedExceptionMessage()
     * @group setAdditionalMessage()
     * 
     * @testdox getFormattedExceptionMessage() return value appends optional setAdditionalMessage()'s arg
     */
    
    public function testSetAdditionalInfoAddsMoreLinesToEndOfMessage()
    {
        $previousExceptionMessage = uniqid();
        $queryString = uniqid();
        $additionalMessage = uniqid();
        
        $SUT = $this->returnSubjectUnderTest();
        
        $SUT->setPreviousExceptionMessage($previousExceptionMessage);
        $SUT->setQueryString($queryString);
        $SUT->setAdditionalMessage($additionalMessage);
        
        $expectedExceptionMessage = $additionalMessage;
        
        $SUT->run();
        
        $this->assertStringEndsWith(
            $expectedExceptionMessage,
            $SUT->getFormattedExceptionMessage()
        );
    }
    
    private function returnSubjectUnderTest()
    {
        return new FormatExceptionMessageWithMaximumInfo();
    }
    
    private function setRequiredSettersExceptAsSpecified(
        $SUT,
        array $specified
    )
    {
        if (!in_array('setPreviousExceptionMessage',$specified)) {
            $SUT->setPreviousExceptionMessage(uniqid());
        }
        if (!in_array('setQueryString',$specified)) {
            $SUT->setQueryString(uniqid());
        }
    }
}

