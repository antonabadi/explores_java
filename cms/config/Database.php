<?php

class Database
{
    private static ?PDO $instance = null;

    private string $host = 'localhost';
    private string $port = '3306';
    private string $dbname = 'explores_java';
    private string $username = 'root';
    private string $password = 'root1234';
    private string $charset = 'utf8mb4';

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $db = new self();
            $dsn = "mysql:host={$db->host};port={$db->port};dbname={$db->dbname};charset={$db->charset}";

            try {
                self::$instance = new PDO($dsn, $db->username, $db->password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
