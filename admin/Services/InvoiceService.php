<?php
namespace App\Admin\Services;

use App\Core\Database;
use App\Repositories\{InvoiceRepository, SettingsRepository};

/**
 * Generates GST invoices — one per (order, seller) pair, since each
 * seller is the seller-of-record for their portion of a multi-seller
 * order and GST law requires the invoice to name the actual seller.
 *
 * Tax logic:
 *   - Product prices are stored tax-inclusive (checkout doesn't add
 *     tax on top), so the taxable value is reverse-calculated from
 *     the line total: taxable = line_total / (1 + gst_rate/100).
 *   - Place of supply = customer's shipping state.
 *   - If seller's registered state === customer's shipping state:
 *     CGST + SGST, split evenly (half the rate each).
 *   - Otherwise: IGST at the full rate.
 *   - If the seller has no registered state on file, the invoice
 *     still generates (so orders never block on missing KYC) but
 *     defaults to treating the sale as interstate — the safer
 *     assumption, since under-charging IGST is a bigger compliance
 *     risk than over-itemizing it.
 */
class InvoiceService
{
    private Database $db;
    private InvoiceRepository $invoices;
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->db       = Database::getInstance();
        $this->invoices = new InvoiceRepository();
        $this->settings = new SettingsRepository();
    }

    /**
     * Generate (or return the existing) invoice for a given
     * order_seller_split. Idempotent — safe to call more than once.
     */
    public function generateForSplit(int $splitId): array
    {
        $existing = $this->invoices->findBySplitId($splitId);
        if ($existing) {
            return ['success' => true, 'invoice_id' => $existing['id'], 'already_existed' => true];
        }

        $split = $this->db->fetchOne(
            "SELECT ovs.*, o.order_number, o.user_id, o.shipping_state
             FROM `" . DB_PREFIX . "order_seller_splits` ovs
             JOIN `" . DB_PREFIX . "orders` o ON o.id = ovs.order_id
             WHERE ovs.id = ?", [$splitId]
        );
        if (!$split) {
            return ['success' => false, 'message' => 'Order seller split not found.'];
        }

        $sellerState = $this->db->fetchOne(
            "SELECT state FROM `" . DB_PREFIX . "seller_profiles` WHERE user_id = ?", [$split['seller_id']]
        )['state'] ?? null;

        $isInterstate = !$sellerState || strtolower(trim($sellerState)) !== strtolower(trim($split['shipping_state']));

        $orderItems = $this->db->fetchAll(
            "SELECT oi.*, p.hsn_code, p.gst_rate
             FROM `" . DB_PREFIX . "order_items` oi
             LEFT JOIN `" . DB_PREFIX . "products` p ON p.id = oi.product_id
             WHERE oi.order_id = ? AND oi.seller_id = ?",
            [$split['order_id'], $split['seller_id']]
        );
        if (empty($orderItems)) {
            return ['success' => false, 'message' => 'No line items found for this seller split.'];
        }

        $lineItems = [];
        $totalTaxable = 0.0; $totalCgst = 0.0; $totalSgst = 0.0; $totalIgst = 0.0;

        foreach ($orderItems as $oi) {
            $gstRate    = (float) ($oi['gst_rate'] ?? 18.00);
            $lineTotal  = (float) $oi['subtotal'];
            $taxable    = round($lineTotal / (1 + $gstRate / 100), 2);
            $taxAmount  = round($lineTotal - $taxable, 2);

            $cgst = $sgst = $igst = 0.0;
            if ($isInterstate) {
                $igst = $taxAmount;
            } else {
                $cgst = round($taxAmount / 2, 2);
                $sgst = $taxAmount - $cgst;
            }

            $totalTaxable += $taxable;
            $totalCgst    += $cgst;
            $totalSgst    += $sgst;
            $totalIgst    += $igst;

            $lineItems[] = [
                'order_item_id'  => $oi['id'],
                'product_name'   => $oi['product_name'],
                'hsn_code'       => $oi['hsn_code'],
                'quantity'       => $oi['quantity'],
                'unit_price'     => $oi['unit_price'],
                'taxable_amount' => $taxable,
                'gst_rate'       => $gstRate,
                'cgst_amount'    => $cgst,
                'sgst_amount'    => $sgst,
                'igst_amount'    => $igst,
                'line_total'     => $lineTotal,
            ];
        }

        $totalTax   = round($totalCgst + $totalSgst + $totalIgst, 2);
        $grandTotal = round($totalTaxable + $totalTax, 2);

        $invoiceId = $this->invoices->insertWithItems([
            'invoice_number'        => $this->nextInvoiceNumber(),
            'order_id'              => $split['order_id'],
            'order_seller_split_id' => $splitId,
            'seller_id'             => $split['seller_id'],
            'user_id'               => $split['user_id'],
            'invoice_date'          => date('Y-m-d'),
            'place_of_supply'       => $split['shipping_state'],
            'is_interstate'         => (int) $isInterstate,
            'taxable_amount'        => $totalTaxable,
            'cgst_amount'           => $totalCgst,
            'sgst_amount'           => $totalSgst,
            'igst_amount'           => $totalIgst,
            'total_tax'             => $totalTax,
            'grand_total'           => $grandTotal,
        ], $lineItems);

        return ['success' => true, 'invoice_id' => $invoiceId, 'already_existed' => false];
    }

    /** Generates invoices for every seller split on an order (called right after checkout). */
    public function generateForOrder(int $orderId): array
    {
        $splits = $this->db->fetchAll(
            "SELECT id FROM `" . DB_PREFIX . "order_seller_splits` WHERE order_id = ?", [$orderId]
        );
        $results = [];
        foreach ($splits as $s) {
            $results[] = $this->generateForSplit((int) $s['id']);
        }
        return $results;
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $last = $this->db->fetchOne(
            "SELECT invoice_number FROM `" . DB_PREFIX . "invoices` WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . '%']
        );
        $seq = $last ? ((int) substr($last['invoice_number'], -5)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
