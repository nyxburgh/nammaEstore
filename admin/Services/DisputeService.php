<?php
namespace App\Admin\Services;

use App\Repositories\DisputeRepository;
use App\Core\Services\NotificationService;

class DisputeService
{
    private DisputeRepository $disputes;

    public function __construct()
    {
        $this->disputes = new DisputeRepository();
    }

    public function queue(int $page, array $filters): array
    {
        return $this->disputes->getQueue($page, $filters);
    }

    public function resolve(int $id, string $note): void
    {
        $this->disputes->resolve($id, $note);

        $d = $this->disputes->findById($id);
        if ($d) {
            (new NotificationService())->notify(
                'seller', (int) $d['seller_id'], 'dispute',
                'Dispute resolved',
                'The dispute on order #' . $d['order_id'] . ' has been resolved.' . ($note ? " Note: {$note}" : ''),
                SELLER_URL . '/orders'
            );
        }
    }
}
