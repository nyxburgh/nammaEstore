<?php
// app/core/Database.php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $cfg = require BASE_PATH . '/config/database.php';
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
        try {
            $this->pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
        } catch (PDOException $e) {
            die(APP_ENV === 'development'
                ? '<b>DB Connection Failed:</b> ' . $e->getMessage()
                : 'Service temporarily unavailable.');
        }
    }

    public static function getInstance(): self
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function getConnection(): PDO { return $this->pdo; }

    public function query(string $sql, array $p = []): \PDOStatement
    {
        $s = $this->pdo->prepare($sql);
        $s->execute($p);
        return $s;
    }

    public function fetchOne(string $sql, array $p = []): ?array
    {
        $r = $this->query($sql, $p)->fetch();
        return $r ?: null;
    }

    public function fetchAll(string $sql, array $p = []): array
    {
        return $this->query($sql, $p)->fetchAll();
    }

    public function insert(string $sql, array $p = []): int
    {
        $this->query($sql, $p);
        return (int) $this->pdo->lastInsertId();
    }

    public function execute(string $sql, array $p = []): int
    {
        return $this->query($sql, $p)->rowCount();
    }

    public function paginate(string $sql, array $p = [], int $page = 1, int $perPage = ITEMS_PER_PAGE): array
    {
        $countSql   = preg_replace('/\s+ORDER\s+BY\s+.+$/is', '', $sql);
        $total      = (int) ($this->fetchOne("SELECT COUNT(*) as c FROM ({$countSql}) x", $p)['c'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset     = ($page - 1) * $perPage;
        $data       = $this->fetchAll("{$sql} LIMIT {$perPage} OFFSET {$offset}", $p);
        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void           { $this->pdo->commit(); }
    public function rollBack(): void         { $this->pdo->rollBack(); }

    private function __clone() {}
}
