<?php
namespace ParasitePDO\hosts;

use PHPUnit\Framework\TestCase;

class ParasitePDOExceptionTest extends TestCase
{
    /**
     * @testdox __construct() can accept non-integer $code values for the second argument, and return them from getCode(), unmodified.
     */
    
    public function testConstructorAcceptsNonIntegerCodes()
    {
        $e = new ParasitePDOException(
            $message=uniqid(),
            $code=uniqid().'blah'.uniqid(),
            $previous=new \PDOException()
        );
        
        $this->assertSame($message,$e->getMessage());
        $this->assertSame($code,$e->getCode());
        $this->assertSame($previous,$e->getPrevious());
    }
}

