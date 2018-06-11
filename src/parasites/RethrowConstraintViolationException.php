<?php
namespace ParasitePDO\parasites;

use ParasitePDO\exceptions\DuplicateKeyException;

class RethrowConstraintViolationException implements IRethrowException
{
    public function __construct(\PDOException $PDOException)
    {
        $code = $PDOException->getCode();
        if ($code >= 23000 && $code < 24000) {
            $isMySQLDuplicateKeyException
                = false !== strpos(
                    $PDOException->getMessage(),
                    $this->mysqlDuplicateKeyString
                );
            if ($isMySQLDuplicateKeyException) {
                throw new DuplicateKeyException();
            }
        }
    }
    
    private $mysqlDuplicateKeyString = 'Integrity constraint violation: 1062 Duplicate entry';
}

