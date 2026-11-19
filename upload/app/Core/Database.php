<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            // Đồng bộ múi giờ MySQL theo Việt Nam.
            $this->connection->exec("SET time_zone = '+07:00'");
        } catch (PDOException $exception) {
            error_log('NEWS HUB PDO connection failed: ' . $exception->getMessage());
            throw new RuntimeException('The database connection is unavailable.');
        }

        return $this->connection;
    }
}
