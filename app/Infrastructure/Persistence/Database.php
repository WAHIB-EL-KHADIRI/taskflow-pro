<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private int $queryCount = 0;
    private array $log = [];

    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $name = $_ENV['DB_DATABASE'] ?? 'taskflow_pro';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        try {
            $this->pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset={$charset}",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $this->queryCount++;
        $this->log[] = compact('sql', 'params');

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $values = array_values($data);

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $values);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = implode(', ', array_map(fn($key) => "{$key} = ?", array_keys($data)));
        $values = array_merge(array_values($data), $whereParams);

        $sql = "UPDATE {$table} SET {$sets} WHERE {$where}";
        return $this->query($sql, $values)->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->query("DELETE FROM {$table} WHERE {$where}", $params)->rowCount();
    }

    public function tableExists(string $table): bool
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE {$this->pdo->quote($table)}");
        return (bool)$stmt->fetch();
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function getLog(): array
    {
        return $this->log;
    }
}
