<?php
namespace ParasitePDO\formatters;

class FormatExceptionMessageWithMaximumInfoFactory
{
    /**
     * @return IFormatExceptionMessage
     */
    
    public function build()
    {
        $Subject = new FormatExceptionMessageWithMaximumInfo();
        
        return $Subject;
    }
}

