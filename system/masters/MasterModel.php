<?php


/**
 * Class MasterModel.
 */
class MasterModel extends Core
{
    /**
     * Gets the correct database configuration, and returns an instance
     * of a class containing the database connection.
     *
     * @param string $db_name_to_connect
     * @return DbInstance
     */
    public function dbConnect(string $db_name_to_connect)
    {
        // All databases configurations.
        $db_configs = UserConfigManager::getConfig()["database"];

        $db_names = []; // Db names.
        foreach ($db_configs as $key => $value) {
            $db_names[] = $key;
        }

        // If the database does not exists.
        if (!in_array($db_name_to_connect, $db_names)) {
            // TODO: set error.
            p("Error: \"" . $db_name_to_connect . "\" name given in \"connectDb()\" is not a valid database. Make sure the database is declared in \"app/config/database::\$database[\"DATABASE_NAME\"]\"", "error");
            die;
        }

        // The selected database configuration.
        $db_config = $db_configs[$db_name_to_connect];

        $host = $db_config["host"];
        $db   = $db_config["db"];
        $user = $db_config["user"];
        $pass = $db_config["pass"];

        // Establishes a new database connection and returns it.
        $db_connect = new DbConnect();
        $pdo = $db_connect->connect($host, $db, $user, $pass);

        // Builds an instance of the database (class with the database connexion).
        $db_instance = new DbInstance();
        $db_instance->setPdo($pdo);

        return $db_instance;
    }
}