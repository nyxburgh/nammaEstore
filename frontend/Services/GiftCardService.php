<?php
namespace App\Frontend\Services;

use App\Repositories\GiftCardRepository;

class GiftCardService
{
    private GiftCardRepository $giftCards;

    public function __construct()
    {
        $this->giftCards = new GiftCardRepository();
    }

    /** Read-only validation — does not redeem. Redemption happens post-order via CheckoutService. */
    public function validate(string $code): array
    {
        $card = $this->giftCards->findByCode($code);
        if (!$card) {
            return ['valid' => false, 'message' => 'Invalid gift card code.'];
        }
        if ($card['status'] !== 'active') {
            return ['valid' => false, 'message' => 'This gift card is ' . $card['status'] . '.'];
        }
        if ($card['expires_at'] && strtotime($card['expires_at']) < time()) {
            return ['valid' => false, 'message' => 'This gift card has expired.'];
        }
        if ((float) $card['current_balance'] <= 0) {
            return ['valid' => false, 'message' => 'This gift card has no remaining balance.'];
        }
        return ['valid' => true, 'card' => $card];
    }
}
