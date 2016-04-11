<?php

namespace Invoicing\Database\Connection;

use PDO;

class Connection
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var PDO|null
     */
    private $instance;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $databaseName
     */
    public function __construct($host, $user, $password, $databaseName)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->databaseName = $databaseName;
    }

    /**
     * @param  string        $statement
     * @return \PDOStatement
     */
    public function prepare($statement)
    {
        return $this->getPDO()->prepare($statement);
    }

    /**
     * @return string
     */
    public function lastInsertId($sequence)
    {
        return $this->getPDO()->lastInsertId($sequence);
    }

    /**
     * @return PDO
     */
    private function getPDO()
    {
        if (!$this->instance) {
            $this->instance = new PDO(
                'pgsql:dbname=' . $this->databaseName . ';host=' . $this->host,
                $this->user,
                $this->password
            );
        }

        return $this->instance;
    }
}
