<?php
namespace ParasitePDO\parasites;

use ParasitePDO\formatters\FormatExceptionMessageWithMaximumInfoFactory;

class RethrowTransactionRollbackExceptionFactory
{
    public function build()
    {
        $Subject = new RethrowTransactionRollbackException();
        
        $Subject->setFormatExceptionMessage(
            (new FormatExceptionMessageWithMaximumInfoFactory())
            ->build()
        );
        
        return $Subject;
    }
}

