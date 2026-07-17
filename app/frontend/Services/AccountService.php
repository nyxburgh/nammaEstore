<?php
namespace App\Frontend\Services;
use App\Core\{Auth, Database};
use App\Repositories\UserRepository;

class AccountService
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function getOrders(int $userId, int $page = 1, string $status = ''): array
    {
        $where  = "o.user_id = ?";
        $params = [$userId];
        if ($status !== '') { $where .= " AND o.order_status = ?"; $params[] = $status; }

        $sql = "SELECT o.*,
                       (SELECT COUNT(*) FROM `".DB_PREFIX."order_items` oi WHERE oi.order_id = o.id) AS item_count
                FROM `".DB_PREFIX."orders` o
                WHERE {$where}
                ORDER BY o.placed_at DESC";

        return $this->db->paginate($sql, $params, $page);
    }

    public function getOrderDetail(int $orderId, int $userId): ?array
    {
        $o = $this->db->fetchOne(
            "SELECT * FROM `".DB_PREFIX."orders` WHERE id = ? AND user_id = ?",
            [$orderId, $userId]
        );
        if (!$o) return null;

        $o['items'] = $this->db->fetchAll(
            "SELECT oi.*,
                    (SELECT image_path FROM `".DB_PREFIX."product_images`
                     WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1) AS image
             FROM `".DB_PREFIX."order_items` oi
             WHERE oi.order_id = ?",
            [$orderId]
        );
        $o['timeline'] = $this->db->fetchAll(
            "SELECT * FROM `".DB_PREFIX."order_status_timeline`
             WHERE order_id = ? ORDER BY created_at ASC",
            [$orderId]
        );
        $o['invoices'] = $this->db->fetchAll(
            "SELECT i.id, i.invoice_number, i.grand_total, u.name as vendor_name, vp.shop_name
             FROM `".DB_PREFIX."invoices` i
             JOIN `".DB_PREFIX."users` u ON u.id = i.vendor_id
             LEFT JOIN `".DB_PREFIX."vendor_profiles` vp ON vp.user_id = i.vendor_id
             WHERE i.order_id = ?",
            [$orderId]
        );
        return $o;
    }

    public function getAddresses(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `".DB_PREFIX."user_addresses`
             WHERE user_id = ? ORDER BY is_default DESC, id DESC",
            [$userId]
        );
    }

    public function addAddress(int $userId, array $d): int
    {
        if (!empty($d['is_default'])) {
            $this->db->execute(
                "UPDATE `".DB_PREFIX."user_addresses` SET is_default = 0 WHERE user_id = ?",
                [$userId]
            );
        }
        return $this->db->insert(
            "INSERT INTO `".DB_PREFIX."user_addresses`
             (user_id, label, recipient_name, phone, address_line1,
              city, state, pincode, is_default)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [
                $userId,
                $d['label']          ?? 'Home',
                $d['recipient_name'] ?? '',
                $d['phone']          ?? '',
                $d['address_line1']  ?? '',
                $d['city']           ?? '',
                $d['state']          ?? '',
                $d['pincode']        ?? '',
                (int)!empty($d['is_default']),
            ]
        );
    }

    public function deleteAddress(int $id, int $userId): void
    {
        $this->db->execute(
            "DELETE FROM `".DB_PREFIX."user_addresses` WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public function setDefault(int $id, int $userId): void
    {
        $this->db->execute(
            "UPDATE `".DB_PREFIX."user_addresses` SET is_default = 0 WHERE user_id = ?",
            [$userId]
        );
        $this->db->execute(
            "UPDATE `".DB_PREFIX."user_addresses` SET is_default = 1 WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public function updateProfile(int $userId, array $d): array
    {
        $um = new UserRepository();
        if (!empty($d['email'])) {
            $current = $um->findById($userId);
            if ($d['email'] !== $current['email']) {
                $existing = $um->findByEmail($d['email']);
                if ($existing) return ['success' => false, 'message' => 'Email already in use.'];
            }
        }
        $update = array_filter([
            'name'  => $d['name']  ?? null,
            'phone' => $d['phone'] ?? null,
            'email' => $d['email'] ?? null,
        ], fn($v) => $v !== null && $v !== '');

        if (!empty($update)) $um->update($userId, $update);

        // Refresh session
        $fresh = $um->findById($userId);
        $_SESSION['user']['name']  = $fresh['name'];
        $_SESSION['user']['email'] = $fresh['email'];

        return ['success' => true];
    }

    public function updatePassword(int $userId, string $current, string $new): array
    {
        $u = (new UserRepository())->findById($userId);
        if (!password_verify($current, $u['password']))
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        if (strlen($new) < 8)
            return ['success' => false, 'message' => 'New password must be at least 8 characters.'];

        (new UserRepository())->update($userId, ['password' => password_hash($new, PASSWORD_DEFAULT)]);
        return ['success' => true, 'message' => 'Password updated successfully.'];
    }

    public function getStats(int $userId): array
    {
        return [
            'total_orders'   => (int)($this->db->fetchOne(
                "SELECT COUNT(*) c FROM `".DB_PREFIX."orders` WHERE user_id = ?",
                [$userId])['c'] ?? 0),
            'pending_orders' => (int)($this->db->fetchOne(
                "SELECT COUNT(*) c FROM `".DB_PREFIX."orders`
                 WHERE user_id = ? AND order_status NOT IN ('delivered','cancelled')",
                [$userId])['c'] ?? 0),
            'wishlist_count' => (int)($this->db->fetchOne(
                "SELECT COUNT(*) c FROM `".DB_PREFIX."wishlists` WHERE user_id = ?",
                [$userId])['c'] ?? 0),
            'total_spent'    => (float)($this->db->fetchOne(
                "SELECT COALESCE(SUM(total),0) s FROM `".DB_PREFIX."orders`
                 WHERE user_id = ? AND payment_status = 'paid'",
                [$userId])['s'] ?? 0),
        ];
    }

    public function getTransactions(int $userId, int $limit = 30): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `" . DB_PREFIX . "payments` WHERE user_id=? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
