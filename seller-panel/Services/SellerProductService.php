<?php
namespace App\SellerPanel\Services;
use App\Core\Database;

class SellerProductService
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAll(int $sellerId, int $page = 1, array $f = []): array {
        $w = "p.seller_id=?"; $params = [$sellerId];
        if (!empty($f['status']) && $f['status'] !== 'all') { $w .= " AND p.status=?"; $params[] = $f['status']; }
        if (!empty($f['category']))  { $w .= " AND p.category_id=?"; $params[] = $f['category']; }
        if (!empty($f['q']))         { $w .= " AND (p.name LIKE ? OR p.sku LIKE ?)"; $s = '%'.$f['q'].'%'; $params[] = $s; $params[] = $s; }
        $sql = "SELECT p.*, c.name as cat_name, COALESCE(SUM(oi.quantity),0) as sold,
                       (SELECT image_path FROM `".DB_PREFIX."product_images` pi WHERE pi.product_id=p.id AND pi.is_primary=1 LIMIT 1) as image
                FROM `".DB_PREFIX."products` p
                LEFT JOIN `".DB_PREFIX."categories` c ON c.id=p.category_id
                LEFT JOIN `".DB_PREFIX."order_items` oi ON oi.product_id=p.id
                WHERE $w GROUP BY p.id ORDER BY p.created_at DESC";
        return $this->db->paginate($sql, $params, $page);
    }

    public function findById(int $id, int $sellerId): ?array {
        $p = $this->db->fetchOne(
            "SELECT p.*, c.name as cat_name FROM `".DB_PREFIX."products` p LEFT JOIN `".DB_PREFIX."categories` c ON c.id=p.category_id WHERE p.id=? AND p.seller_id=?",
            [$id, $sellerId]);
        if (!$p) return null;
        $p['images']   = $this->db->fetchAll("SELECT * FROM `".DB_PREFIX."product_images` WHERE product_id=? ORDER BY sort_order", [$id]);
        $p['variants'] = $this->db->fetchAll("SELECT * FROM `".DB_PREFIX."product_variants` WHERE product_id=?", [$id]);
        return $p;
    }

    /** Only these GST slabs are offered in the product form — reject anything else server-side. */
    private const GST_RATES = [0.0, 5.0, 12.0, 18.0, 28.0];

    private function normalizeGstRate(array $d): float {
        $rate = isset($d['gst_rate']) ? (float)$d['gst_rate'] : 18.0;
        return in_array($rate, self::GST_RATES, true) ? $rate : 18.0;
    }

    public function create(int $sellerId, array $d, array $files = []): array {
        if (empty($d['name']))  return ['success'=>false,'message'=>'Product name is required.'];
        if (empty($d['price'])) return ['success'=>false,'message'=>'Price is required.'];
        if (empty($d['category_id'])) return ['success'=>false,'message'=>'Category is required.'];
        $slug = \slugify($d['name']) . '-' . uniqid();
        $id = $this->db->insert(
            "INSERT INTO `".DB_PREFIX."products` (seller_id,category_id,name,slug,description,price,sale_price,stock,sku,barcode,hsn_code,gst_rate,weight,is_featured,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$sellerId,$d['category_id'],$d['name'],$slug,$d['description']??null,max(0,(float)$d['price']),!empty($d['sale_price'])?max(0,(float)$d['sale_price']):null,max(0,(int)($d['stock']??0)),$d['sku']??null,$d['barcode']??null,$d['hsn_code']??null,$this->normalizeGstRate($d),!empty($d['weight'])?max(0,(float)$d['weight']):null,(int)!empty($d['is_featured']),$d['status']??'active']
        );
        $this->handleImages($id, $files);
        $this->handleVariants($id, $d['variants'] ?? []);
        return ['success'=>true,'id'=>$id];
    }

    public function update(int $id, int $sellerId, array $d, array $files = []): array {
        $p = $this->findById($id, $sellerId);
        if (!$p) return ['success'=>false,'message'=>'Product not found.'];
        $this->db->execute(
            "UPDATE `".DB_PREFIX."products` SET category_id=?,name=?,description=?,price=?,sale_price=?,stock=?,sku=?,barcode=?,hsn_code=?,gst_rate=?,weight=?,is_featured=?,status=? WHERE id=? AND seller_id=?",
            [$d['category_id'],$d['name'],$d['description']??null,max(0,(float)$d['price']),!empty($d['sale_price'])?max(0,(float)$d['sale_price']):null,max(0,(int)($d['stock']??0)),$d['sku']??null,$d['barcode']??null,$d['hsn_code']??null,$this->normalizeGstRate($d),!empty($d['weight'])?max(0,(float)$d['weight']):null,(int)!empty($d['is_featured']),$d['status']??'active',$id,$sellerId]
        );
        $this->handleImages($id, $files);
        if (isset($d['variants'])) {
            $this->db->execute("DELETE FROM `".DB_PREFIX."product_variants` WHERE product_id=?", [$id]);
            $this->handleVariants($id, $d['variants']);
        }
        return ['success'=>true];
    }

    public function delete(int $id, int $sellerId): void {
        $this->db->execute("DELETE FROM `".DB_PREFIX."products` WHERE id=? AND seller_id=?", [$id, $sellerId]);
    }

    public function toggle(int $id, int $sellerId): string {
        $p = $this->db->fetchOne("SELECT status FROM `".DB_PREFIX."products` WHERE id=? AND seller_id=?", [$id, $sellerId]);
        if (!$p) return 'not_found';
        $new = $p['status'] === 'active' ? 'inactive' : 'active';
        $this->db->execute("UPDATE `".DB_PREFIX."products` SET status=? WHERE id=? AND seller_id=?", [$new, $id, $sellerId]);
        return $new;
    }

    private function handleImages(int $productId, array $files): void {
        if (empty($files['images']['name'][0])) return;
        $existing = $this->db->fetchOne("SELECT COUNT(*) c FROM `".DB_PREFIX."product_images` WHERE product_id=?", [$productId]);
        $isPrimary = (int)($existing['c'] ?? 0) === 0 ? 1 : 0;
        for ($i = 0; $i < count($files['images']['name']); $i++) {
            if ($files['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $f = ['name'=>$files['images']['name'][$i],'type'=>$files['images']['type'][$i],'tmp_name'=>$files['images']['tmp_name'][$i],'error'=>$files['images']['error'][$i],'size'=>$files['images']['size'][$i]];
            $path = \uploadFile($f, 'products');
            if ($path) {
                $this->db->insert("INSERT INTO `".DB_PREFIX."product_images` (product_id,image_path,is_primary,sort_order) VALUES (?,?,?,?)", [$productId,$path,$isPrimary,$i]);
                $isPrimary = 0;
            }
        }
    }

    private function handleVariants(int $productId, array $variants): void {
        foreach ($variants as $v) {
            if (empty($v['name']) || empty($v['value'])) continue;
            $this->db->insert("INSERT INTO `".DB_PREFIX."product_variants` (product_id,variant_name,variant_value,price_modifier,stock,is_active) VALUES (?,?,?,?,?,1)", [$productId,$v['name'],$v['value'],(float)($v['price_modifier']??0),(int)($v['stock']??0)]);
        }
    }
}
