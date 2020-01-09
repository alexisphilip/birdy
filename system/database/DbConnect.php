<?php


/**
 * Class DbConnect
 */
class DbConnect
{
    /**
     * Establishes a database connection with PDO and returns it.
     *
     * @param $host
     * @param $db
     * @param $user
     * @param $pass
     * @return PDO|string
     */
    public function connect($host, $db, $user, $pass)
    {
        $charset = 'utf8mb4';
        $pdo = "";

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }

        // TODO: delete this?
        return $pdo;
    }
}