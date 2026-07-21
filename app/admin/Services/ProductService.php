<?php
namespace App\Admin\Services;

use App\Repositories\ProductRepository;

class ProductService
{
    private ProductRepository $products;

    public function __construct()
    {
        $this->products = new ProductRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->products->getProducts($page, $filters);
    }

    public function stats(): array
    {
        return $this->products->getStats();
    }

    public function find(int $id): ?array
    {
        return $this->products->getProductDetail($id);
    }

    public function updateStatus(int $id, string $status, int $adminId): bool
    {
        if (!in_array($status, ['active', 'draft', 'inactive', 'rejected'], true)) {
            return false;
        }
        $this->products->updateStatus($id, $status, $adminId);
        return true;
    }

    public function delete(int $id): void
    {
        $this->products->delete($id);
    }

    /**
     * Create or update a product, including its images and variants.
     * Moved out of ProductController — was previously raw SQL in a
     * private controller method.
     */
    public function save(?int $id, array $d, array $files): int
    {
        $slug = $id
            ? ($this->products->getSlug($id) ?? slugify($d['name']) . '-' . uniqid())
            : slugify($d['name']) . '-' . uniqid();

        $data = [
            'seller_id'   => (int) $d['seller_id'],
            'category_id' => $d['category_id'] ?: null,
            'brand_id'    => $d['brand_id'] ?: null,
            'name'        => $d['name'],
            'slug'        => $slug,
            'sku'         => $d['sku'] ?: null,
            'barcode'     => $d['barcode'] ?: null,
            'hsn_code'    => $d['hsn_code'] ?: null,
            'gst_rate'    => isset($d['gst_rate']) ? (float) $d['gst_rate'] : 18.00,
            'description' => $d['description'] ?? null,
            'price'       => (float) $d['price'],
            'sale_price'  => !empty($d['sale_price']) ? (float) $d['sale_price'] : null,
            'stock'       => (int) ($d['stock'] ?? 0),
            'weight'      => !empty($d['weight']) ? (float) $d['weight'] : null,
            'is_featured' => (int) !empty($d['is_featured']),
            'status'      => $d['status'] ?? 'draft',
        ];

        if ($id) {
            $this->products->update($id, $data);
            $productId = $id;
        } else {
            $productId = $this->products->insert($data);
        }

        $this->saveImages($productId, $files);
        $this->saveVariants($productId, $id !== null, $d['variants'] ?? []);

        return $productId;
    }

    private function saveImages(int $productId, array $files): void
    {
        if (empty($files['images']['name'][0])) {
            return;
        }
        $primary = $this->products->imageCount($productId) === 0 ? true : false;

        for ($i = 0; $i < count($files['images']['name']); $i++) {
            if ($files['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $file = [
                'name'     => $files['images']['name'][$i],
                'type'     => $files['images']['type'][$i],
                'tmp_name' => $files['images']['tmp_name'][$i],
                'error'    => 0,
                'size'     => $files['images']['size'][$i],
            ];
            $path = uploadFile($file, 'products');
            if ($path) {
                $this->products->addImage($productId, $path, $primary, $i);
                $primary = false;
            }
        }
    }

    private function saveVariants(int $productId, bool $isUpdate, array $variants): void
    {
        if (empty($variants)) {
            return;
        }
        if ($isUpdate) {
            $this->products->clearVariants($productId);
        }
        foreach ($variants as $v) {
            if (empty($v['name']) || empty($v['value'])) continue;
            $this->products->addVariant(
                $productId,
                $v['name'],
                $v['value'],
                (float) ($v['price_modifier'] ?? 0),
                (int) ($v['stock'] ?? 0)
            );
        }
    }
}
