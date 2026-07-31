<?php
namespace App\Repositories;

use App\Core\Repository;

class SettingsRepository extends Repository
{
    protected string $table = 'settings';

    public function get(string $key, mixed $default = null): mixed
    {
        $row = $this->db->fetchOne("SELECT value FROM `{$this->t()}` WHERE `key`=?", [$key]);
        return $row ? $row['value'] : $default;
    }

    public function getAllGrouped(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM `{$this->t()}` ORDER BY `group`, id");
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['group']][] = $r;
        }
        return $grouped;
    }

    public function updateMany(array $keyValues): void
    {
        foreach ($keyValues as $key => $val) {
            $this->db->execute("UPDATE `{$this->t()}` SET value=? WHERE `key`=?", [$val, $key]);
        }
    }
}
