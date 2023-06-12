<?php
namespace service\database;
require_once __DIR__ . '/../../autoloader.php';

class DatabaseConnection {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $env = require __DIR__ .  '/../../configuration/env.php';
            $dsn = "mysql:host={$env['host']};dbname={$env['dbName']};port={$env['port']};charset=utf8mb4";
            $this->connection = new \PDO($dsn, $env['user'], $env['password']);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public static function getInstance(): self{
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }

    public function getConnection(): \PDO{
        return $this->connection;
    }
}