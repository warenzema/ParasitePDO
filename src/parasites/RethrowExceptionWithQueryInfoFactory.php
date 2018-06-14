<?php
namespace ParasitePDO\parasites;

use ParasitePDO\formatters\FormatExceptionMessageWithMaximumInfoFactory;

class RethrowExceptionWithQueryInfoFactory
{
    /**
     * @return IRethrowException
     */
    
    public function build()
    {
        $Subject = new RethrowExceptionWithQueryInfo();
        
        $Subject->setFormatExceptionMessage(
            (new FormatExceptionMessageWithMaximumInfoFactory())
            ->build()
        );
        
        return $Subject;
    }
}

