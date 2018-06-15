<?php
namespace ParasitePDO\parasites;

use ParasitePDO\hosts\ParasitePDO;

interface IRethrowException
{
    
    public function setPDOException(\PDOException $PDOException);
    
    public function setStatement(string $statement);
    
    public function setBoundInputParams($boundInputParams);
    
    public function setParasitePDO(ParasitePDO $ParasitePDO);
    
    public function run();
}

