<?php
namespace App\Repositories;

use App\Core\Repository;

class SidebarRepository extends Repository
{
    public function getAdminCounts(): array
    {
        return [
            'pending_vendors' => (int) ($this->db->fetchOne(
                "SELECT COUNT(*) c FROM `{$this->t('vendor_profiles')}` WHERE status='pending'"
            )['c'] ?? 0),
            'pending_reviews' => (int) ($this->db->fetchOne(
                "SELECT COUNT(*) c FROM `{$this->t('reviews')}` WHERE is_approved=0 AND is_flagged=0"
            )['c'] ?? 0),
        ];
    }
}
