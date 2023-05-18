<?php

class Db
{

    public function __construct(
        private string $hostname,
        private string $username,
        private string $password,
        private string $database
    )
    {
    }

    public function getConnection(): PDO
    {
        try {
            return new PDO("mysql:host=$this->hostname;dbname=$this->database", $this->username, $this->password);
        } catch (PDOException) {
            throw new \Exception('DB connection failed');
        }
    }

    public function insert(string $table, array $data)
    {
        ksort($data);
        $columns = array_keys($data);
        if (count($columns)) {
            $connection = $this->getConnection();
            $query = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $table,
                implode(',', $columns),
                implode(',', array_fill(0, count($columns), '?'))
            );
            $statement = $connection->prepare($query);
            $statement->execute(array_values($data));

        }
    }
}