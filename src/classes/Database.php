<?php
/**
 * Class Database
 * A Singleton Class that opens a connection to the mysql database
 */
class Database
{
    private static Database $instance;
    protected PDO $pdo;

    private function __construct()
    {
        $config = static::configuration();
        $this->pdo = new PDO(
            'mysql:host=mariadb;dbname=' . $config['db']['name'],
            $config['db']['user'],
            $config['db']['password']
        );
    }

    static public function getInstance(): Database
    {
        if (!isset(static::$instance)) {
            static::$instance = new Database();
        }
        return static::$instance;
    }

    protected static function configuration(): array
    {
        return require(dirname(__DIR__) . '/config.php');
    }

    public function connection(): PDO
    {
        return $this->pdo;
    }
}