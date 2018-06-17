<?php
namespace ParasitePDO\parasites;

use ParasitePDO\formatters\FormatExceptionMessageWithMaximumInfoFactory;

class RethrowLockWaitTimeoutExceptionFactory
{
    /**
     * @return IRethrowException
     */
    
    public function build()
    {
        $Subject = new RethrowLockWaitTimeoutException();
        
        $Subject->setFormatExceptionMessage(
            (new FormatExceptionMessageWithMaximumInfoFactory())
            ->build()
        );
        
        return $Subject;
    }
}

