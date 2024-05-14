<?php
declare(strict_types=1);

trait DbConnectionTrait
{
    //create user `parasitepdouser`@`192.168.56.100` IDENTIFIED BY 'Passpass1!';
    //GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, RELOAD, PROCESS, REFERENCES, INDEX, ALTER, SHOW DATABASES, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, REPLICATION SLAVE, REPLICATION CLIENT, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, CREATE USER, EVENT, TRIGGER ON *.* to `parasitepdouser`@`192.168.56.100` WITH GRANT OPTION;
    private $dsn = 'mysql:host=192.168.56.101';
    private $username = 'parasitepdouser';
    private $password = 'Passpass1!';
    private $dbname = 'parasitepdotest';
    private $tablename = 'parasite_pdo_test_table';
}