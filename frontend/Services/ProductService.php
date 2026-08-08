<?php
namespace App\Frontend\Services;
use App\Core\Database;

class ProductService
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    private function baseSelect(): string
    {
        return "SELECT p.*,
                       (SELECT image_path FROM `".DB_PREFIX."product_images`
                        WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image,
                       vp.shop_name,
                       c.name AS category_name, c.slug AS category_slug
                FROM `".DB_PREFIX."products` p
                LEFT JOIN `".DB_PREFIX."seller_profiles` vp ON vp.user_id = p.seller_id
                LEFT JOIN `".DB_PREFIX."categories` c ON c.id = p.category_id";
    }

    public function getFeatured(int $limit = 10): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . " WHERE p.status='active' AND p.is_featured=1 ORDER BY RAND() LIMIT ?",
            [$limit]
        );
    }

    public function getTrending(int $limit = 10): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . " WHERE p.status='active' ORDER BY p.views DESC LIMIT ?",
            [$limit]
        );
    }

    public function getNewArrivals(int $limit = 10): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . " WHERE p.status='active' ORDER BY p.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function getDeals(int $limit = 4): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . " WHERE p.status='active'
              AND p.sale_price IS NOT NULL AND p.sale_price < p.price
              ORDER BY ((p.price - p.sale_price) / p.price) DESC LIMIT ?",
            [$limit]
        );
    }

    public function getByCategory(string $slug, int $page = 1, array $filters = []): array
    {
        $cat = $this->db->fetchOne(
            "SELECT * FROM `".DB_PREFIX."categories` WHERE slug = ? AND is_active = 1",
            [$slug]
        );
        if (!$cat) return ['category' => null, 'products' => []];

        // The URL's category is always included; extra categories checked
        // in the sidebar (filters['categories']) widen the result set.
        $catIds = array_unique(array_merge([(int)$cat['id']], array_map('intval', $filters['categories'] ?? [])));
        $placeholders = implode(',', array_fill(0, count($catIds), '?'));
        $where  = "p.status = 'active' AND p.category_id IN ({$placeholders})";
        $params = $catIds;

        if (!empty($filters['min_price'])) {
            $where .= " AND COALESCE(p.sale_price, p.price) >= ?";
            $params[] = (float)$filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where .= " AND COALESCE(p.sale_price, p.price) <= ?";
            $params[] = (float)$filters['max_price'];
        }

        $order = $this->sortClause($filters['sort'] ?? 'newest');
        $sql   = $this->baseSelect() . " WHERE {$where} ORDER BY {$order}";

        return [
            'category' => $cat,
            'products' => $this->db->paginate($sql, $params, $page),
        ];
    }

    public function search(string $q, int $page = 1, array $filters = []): array
    {
        $s      = '%' . $q . '%';
        $where  = "p.status = 'active' AND (p.name LIKE ? OR p.description LIKE ? OR vp.shop_name LIKE ?)";
        $params = [$s, $s, $s];

        if (!empty($filters['category'])) {
            $where   .= " AND p.category_id = ?";
            $params[] = (int)$filters['category'];
        }
        if (!empty($filters['seller_id'])) {
            $where   .= " AND p.seller_id = ?";
            $params[] = (int)$filters['seller_id'];
        }

        $order = $this->sortClause($filters['sort'] ?? 'popular');
        $sql   = $this->baseSelect() . " WHERE {$where} ORDER BY {$order}";

        return $this->db->paginate($sql, $params, $page);
    }

    public function getAll(int $page = 1, array $filters = []): array
    {
        $where  = "p.status = 'active'";
        $params = [];

        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $catIds = array_map('intval', $filters['categories']);
            $placeholders = implode(',', array_fill(0, count($catIds), '?'));
            $where   .= " AND p.category_id IN ({$placeholders})";
            array_push($params, ...$catIds);
        } elseif (!empty($filters['category'])) {
            $where   .= " AND p.category_id = ?";
            $params[] = (int)$filters['category'];
        }
        if (!empty($filters['seller_id'])) {
            $where   .= " AND p.seller_id = ?";
            $params[] = (int)$filters['seller_id'];
        }
        if (!empty($filters['min_price'])) {
            $where   .= " AND COALESCE(p.sale_price, p.price) >= ?";
            $params[] = (float)$filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where   .= " AND COALESCE(p.sale_price, p.price) <= ?";
            $params[] = (float)$filters['max_price'];
        }

        $order = $this->sortClause($filters['sort'] ?? 'newest');
        $sql   = $this->baseSelect() . " WHERE {$where} ORDER BY {$order}";

        return $this->db->paginate($sql, $params, $page);
    }

    public function getBySlug(string $slug): ?array
    {
        $p = $this->db->fetchOne(
            "SELECT p.*,
                    c.name AS category_name, c.slug AS category_slug,
                    u.name AS seller_name, vp.shop_name, vp.shop_slug
             FROM `".DB_PREFIX."products` p
             LEFT JOIN `".DB_PREFIX."categories` c ON c.id = p.category_id
             LEFT JOIN `".DB_PREFIX."users` u ON u.id = p.seller_id
             LEFT JOIN `".DB_PREFIX."seller_profiles` vp ON vp.user_id = p.seller_id
             WHERE p.slug = ? AND p.status = 'active'",
            [$slug]
        );
        if (!$p) return null;

        $p['images']   = $this->db->fetchAll(
            "SELECT * FROM `".DB_PREFIX."product_images`
             WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC",
            [$p['id']]
        );
        $p['variants'] = $this->db->fetchAll(
            "SELECT * FROM `".DB_PREFIX."product_variants`
             WHERE product_id = ? AND is_active = 1",
            [$p['id']]
        );

        // Increment views
        $this->db->execute(
            "UPDATE `".DB_PREFIX."products` SET views = views + 1 WHERE id = ?",
            [$p['id']]
        );
        return $p;
    }

    public function getRelated(int $productId, int $categoryId, int $limit = 5): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . "
            WHERE p.status = 'active' AND p.category_id = ? AND p.id != ?
            ORDER BY RAND() LIMIT ?",
            [$categoryId, $productId, $limit]
        );
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `" . DB_PREFIX . "products` WHERE id=?", [$id]);
    }

    /** Fetches a variant, scoped to the given product so a variant_id can't be cross-applied to a different product's price. */
    public function getVariant(int $variantId, int $productId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `" . DB_PREFIX . "product_variants` WHERE id=? AND product_id=? AND is_active=1",
            [$variantId, $productId]
        );
    }

    public function getSellerStoreBySlug(string $slug): ?array
    {
        return $this->db->fetchOne(
            "SELECT vp.*, u.name AS owner_name
             FROM `" . DB_PREFIX . "seller_profiles` vp
             JOIN `" . DB_PREFIX . "users` u ON u.id = vp.user_id
             WHERE vp.shop_slug = ? AND vp.status = 'active'",
            [$slug]
        );
    }

    public function getCategories(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `".DB_PREFIX."categories`
             WHERE is_active = 1 AND parent_id IS NULL
             ORDER BY sort_order ASC, name ASC"
        );
    }

    private function sortClause(string $sort): string
    {
        return match($sort) {
            'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
            'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
            'popular'    => 'p.views DESC',
            'deals'      => '((p.price - COALESCE(p.sale_price, p.price)) / p.price) DESC',
            default      => 'p.created_at DESC',
        };
    }
}
