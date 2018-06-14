<?php
namespace ParasitePDO\formatters;

interface IFormatExceptionMessage
{
    public function setPreviousExceptionMessage(
        string $previousExceptionMessage
    );
    
    public function setQueryString(string $queryString);
    
    public function setBoundInputParams($boundInputParams);
    
    public function setAdditionalMessage(string $additionalMessage);
    
    public function run();
    
    public function getFormattedExceptionMessage();
}

