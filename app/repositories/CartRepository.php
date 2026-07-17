<?php
namespace App\Repositories;

use App\Core\Repository;

class CartRepository extends Repository
{
    protected string $table = 'carts';

    public function getOrCreate(?int $userId, string $sessionId): array
    {
        $cart = $userId
            ? $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE user_id=?", [$userId])
            : $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE session_id=? AND user_id IS NULL", [$sessionId]);

        if (!$cart) {
            $id = $this->db->insert(
                "INSERT INTO `{$this->t()}` (user_id, session_id) VALUES (?,?)",
                [$userId, $sessionId]
            );
            $cart = ['id' => $id, 'user_id' => $userId, 'session_id' => $sessionId];
        }
        return $cart;
    }

    public function getItems(int $cartId): array
    {
        return $this->db->fetchAll(
            "SELECT ci.*, p.id as product_id_check, p.vendor_id, p.category_id, p.brand_id, p.name, p.slug, p.price, p.sale_price, p.stock, p.gst_rate,
                    (SELECT image_path FROM `{$this->t('product_images')}` WHERE product_id=p.id AND is_primary=1 LIMIT 1) as image,
                    u.name as vendor_name, vp.shop_name,
                    pv.variant_name, pv.variant_value, pv.price_modifier
             FROM `{$this->t('cart_items')}` ci
             JOIN `{$this->t('products')}` p ON p.id=ci.product_id
             JOIN `{$this->t('users')}` u ON u.id=p.vendor_id
             LEFT JOIN `{$this->t('vendor_profiles')}` vp ON vp.user_id=p.vendor_id
             LEFT JOIN `{$this->t('product_variants')}` pv ON pv.id=ci.variant_id
             WHERE ci.cart_id=? AND p.status='active'
             ORDER BY ci.id DESC", [$cartId]
        );
    }

    public function addItem(int $cartId, int $productId, ?int $variantId, int $qty, float $price): void
    {
        $existing = $this->db->fetchOne(
            "SELECT * FROM `{$this->t('cart_items')}` WHERE cart_id=? AND product_id=? AND (variant_id=? OR (variant_id IS NULL AND ? IS NULL))",
            [$cartId, $productId, $variantId, $variantId]
        );
        if ($existing) {
            $this->db->execute(
                "UPDATE `{$this->t('cart_items')}` SET quantity=quantity+?, price=? WHERE id=?",
                [$qty, $price, $existing['id']]
            );
        } else {
            $this->db->insert(
                "INSERT INTO `{$this->t('cart_items')}` (cart_id,product_id,variant_id,quantity,price) VALUES (?,?,?,?,?)",
                [$cartId, $productId, $variantId, $qty, $price]
            );
        }
    }

    public function updateItem(int $cartId, int $itemId, int $qty): void
    {
        $this->db->execute(
            "UPDATE `{$this->t('cart_items')}` SET quantity=? WHERE id=? AND cart_id=?",
            [$qty, $itemId, $cartId]
        );
    }

    public function removeItem(int $cartId, int $itemId): void
    {
        $this->db->execute("DELETE FROM `{$this->t('cart_items')}` WHERE id=? AND cart_id=?", [$itemId, $cartId]);
    }

    public function clearCart(int $cartId): void
    {
        $this->db->execute("DELETE FROM `{$this->t('cart_items')}` WHERE cart_id=?", [$cartId]);
    }

    public function getCount(int $cartId): int
    {
        return (int) ($this->db->fetchOne(
            "SELECT COALESCE(SUM(quantity),0) as c FROM `{$this->t('cart_items')}` WHERE cart_id=?",
            [$cartId]
        )['c'] ?? 0);
    }

    public function mergeGuestCart(string $sessionId, int $userId): void
    {
        $guest = $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE session_id=? AND user_id IS NULL", [$sessionId]);
        if (!$guest) return;
        $userCart = $this->getOrCreate($userId, $sessionId);
        $items = $this->getItems($guest['id']);
        foreach ($items as $item) {
            $this->addItem($userCart['id'], $item['product_id'], $item['variant_id'], $item['quantity'], $item['price']);
        }
        $this->db->execute("DELETE FROM `{$this->t()}` WHERE id=?", [$guest['id']]);
    }
}
