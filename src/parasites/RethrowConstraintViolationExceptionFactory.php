<?php
namespace ParasitePDO\parasites;

use ParasitePDO\formatters\FormatExceptionMessageWithMaximumInfoFactory;

class RethrowConstraintViolationExceptionFactory
{
    /**
     * @return IRethrowException
     */
    
    public function build()
    {
        $Subject = new RethrowConstraintViolationException();
        
        $Subject->setFormatExceptionMessage(
            (new FormatExceptionMessageWithMaximumInfoFactory())
            ->build()
        );
        
        return $Subject;
    }
}

