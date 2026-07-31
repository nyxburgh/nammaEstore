<?php
namespace App\Frontend\Services;
use App\Core\Auth;
use App\Repositories\CartRepository;

class CartService
{
    private CartRepository $model;
    private array $cart;
    private int $cartId;

    public function __construct() {
        $this->model = new CartRepository();
        $sessionId   = session_id();
        $userId      = Auth::userId();
        $this->cart  = $this->model->getOrCreate($userId, $sessionId);
        $this->cartId = $this->cart['id'];
    }

    public function getItems(): array    { return $this->model->getItems($this->cartId); }
    public function getCount(): int      { return $this->model->getCount($this->cartId); }

    public function getTotal(): float {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $price  = (float)($item['sale_price'] ?: $item['price']);
            $price += (float)($item['price_modifier'] ?? 0);
            $total += $price * (int)$item['quantity'];
        }
        return $total;
    }

    public function getSummary(): array {
        $items    = $this->getItems();
        $subtotal = 0;
        $gst      = 0;           // GST component included in the subtotal
        $gstBreakup = [];        // rate (%) => included GST amount
        foreach ($items as $item) {
            $price     = (float)($item['sale_price'] ?: $item['price']);
            $price    += (float)($item['price_modifier'] ?? 0);
            $lineTotal = $price * (int)$item['quantity'];
            $subtotal += $lineTotal;

            // Prices are GST-inclusive: back out the tax portion per item rate
            $rate = (float)($item['gst_rate'] ?? 18.0);
            if ($rate > 0) {
                $lineGst = round($lineTotal * $rate / (100 + $rate), 2);
                $gst += $lineGst;
                $key = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
                $gstBreakup[$key] = round(($gstBreakup[$key] ?? 0) + $lineGst, 2);
            }
        }
        $gst       = round($gst, 2);
        $taxable   = round($subtotal - $gst, 2);
        ksort($gstBreakup, SORT_NUMERIC);
        $shipping  = $subtotal >= 499 ? 0 : 49;
        $discount  = 0;
        $total     = $subtotal + $shipping - $discount;
        return compact('items','subtotal','taxable','gst','gstBreakup','shipping','discount','total');
    }

    public function add(int $productId, int $qty = 1, ?int $variantId = null, float $price = 0): array {
        if ($qty < 1) return ['success' => false, 'message' => 'Invalid quantity.'];
        $this->model->addItem($this->cartId, $productId, $variantId, $qty, $price);
        return ['success' => true, 'count' => $this->getCount()];
    }

    public function update(int $itemId, int $qty): array {
        if ($qty < 1) { $this->model->removeItem($this->cartId, $itemId); }
        else          { $this->model->updateItem($this->cartId, $itemId, $qty); }
        return ['success' => true, 'count' => $this->getCount(), 'total' => $this->getTotal()];
    }

    public function remove(int $itemId): array {
        $this->model->removeItem($this->cartId, $itemId);
        return ['success' => true, 'count' => $this->getCount()];
    }

    public function clear(): void { $this->model->clearCart($this->cartId); }

    public function cartId(): int { return $this->cartId; }
}
