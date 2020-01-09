<?php

/**
 * Class DbInstance.
 *
 * This class will be used in the models as a PDO's instance accessor.
 * $pdo contains the PDO's instance, and execQuery() executes a query
 * on the current's PDO's instance.
 */
class DbInstance
{
    /**
     * PDO's instance.
     *
     * @var $pdo
     */
    private $pdo;

    /**
     * Prepares, executes, and fetches all the data from a SQL query.
     *
     * @param string $query the query.
     *
     * @return mixed
     */
    public function execQuery(string $query): array
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Sets PDO's instance.
     *
     * @param PDO|string $pdo
     */
    public function setPdo($pdo): void
    {
        $this->pdo = $pdo;
    }
}
