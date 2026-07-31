<?php
namespace App\Admin\Services;

use App\Repositories\GiftCardRepository;

class GiftCardService
{
    private GiftCardRepository $giftCards;

    public function __construct()
    {
        $this->giftCards = new GiftCardRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->giftCards->getAll($page, $filters);
    }

    public function find(int $id): ?array
    {
        $card = $this->giftCards->findById($id);
        if ($card) $card['transactions'] = $this->giftCards->getTransactions($id);
        return $card;
    }

    public function issue(array $d): array
    {
        $type = in_array($d['type'] ?? '', ['company', 'seller', 'recharge'], true) ? $d['type'] : 'company';
        $amount = (float) ($d['amount'] ?? 0);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero.'];
        }
        if ($type === 'seller' && empty($d['seller_id'])) {
            return ['success' => false, 'message' => 'Seller gift cards require a seller to be selected.'];
        }

        $code = $this->generateCode();

        $id = $this->giftCards->insert([
            'code'              => $code,
            'type'              => $type,
            'seller_id'         => $type === 'seller' ? (int) $d['seller_id'] : null,
            'initial_balance'   => $amount,
            'current_balance'   => $amount,
            'issued_to_email'   => $d['issued_to_email'] ?? null,
            'status'            => 'active',
            'expires_at'        => $d['expires_at'] ?: null,
        ]);

        $this->giftCards->logIssue($id, $amount);

        return ['success' => true, 'code' => $code, 'id' => $id];
    }

    private function generateCode(): string
    {
        do {
            $code = 'GC-' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->giftCards->findByCode($code));
        return $code;
    }
}
