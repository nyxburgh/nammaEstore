<?php
// app/core/Model.php
namespace App\Core;

abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $pk    = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Prefixed table name helper
    protected function t(string $name = ''): string
    {
        return DB_PREFIX . ($name ?: $this->table);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->t()}` WHERE `{$this->pk}` = ?",
            [$id]
        );
    }

    public function count(string $where = '1=1', array $p = []): int
    {
        return (int) ($this->db->fetchOne(
            "SELECT COUNT(*) as c FROM `{$this->t()}` WHERE {$where}",
            $p
        )['c'] ?? 0);
    }

    public function insert(array $data): int
    {
        $cols   = '`' . implode('`,`', array_keys($data)) . '`';
        $places = implode(',', array_fill(0, count($data), '?'));
        return $this->db->insert(
            "INSERT INTO `{$this->t()}` ({$cols}) VALUES ({$places})",
            array_values($data)
        );
    }

    public function update(int $id, array $data): int
    {
        $set = implode(',', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        return $this->db->execute(
            "UPDATE `{$this->t()}` SET {$set} WHERE `{$this->pk}` = ?",
            [...array_values($data), $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM `{$this->t()}` WHERE `{$this->pk}` = ?",
            [$id]
        );
    }

    public function paginate(string $sql, array $p = [], int $page = 1): array
    {
        return $this->db->paginate($sql, $p, $page);
    }
}
