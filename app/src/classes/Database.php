<?php

namespace App;

use PDO;
use PDOException;
use InvalidArgumentException;

class Database
{
    private PDO $connection;

    public function __construct(string $dsn, string $user, string $password, array $options = [])
    {
        try {
            $this->connection = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $exception) {
            throw new InvalidArgumentException('Ошибка подключения к БД: ' . $exception->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}