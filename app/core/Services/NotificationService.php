<?php
namespace App\Core\Services;

use App\Core\ProviderFactory;
use App\Repositories\NotificationRepository;

/**
 * Single entry point for notifying a customer, seller, or admin.
 * Always writes an in-app notification; optionally also sends an
 * email/SMS through the Phase 0 provider interfaces for the events
 * that matter most (order confirmation, shipment, delivery).
 *
 * Callers never touch ProviderFactory or NotificationRepository
 * directly — everything about "how someone gets told something"
 * lives here, so a future channel (push notifications, in-app
 * banners) is a one-file change.
 */
class NotificationService
{
    private NotificationRepository $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationRepository();
    }

    public function notify(string $userType, int $userId, string $type, string $title, string $message, ?string $link = null): void
    {
        $this->notifications->insert([
            'user_type' => $userType,
            'user_id'   => $userId,
            'type'      => $type,
            'title'     => $title,
            'message'   => $message,
            'link'      => $link,
        ]);
    }

    public function getForUser(string $userType, int $userId, int $page = 1): array
    {
        return $this->notifications->getForUser($userType, $userId, $page);
    }

    public function unreadCount(string $userType, int $userId): int
    {
        return $this->notifications->unreadCount($userType, $userId);
    }

    public function markRead(int $id, string $userType, int $userId): void
    {
        $this->notifications->markRead($id, $userType, $userId);
    }

    public function markAllRead(string $userType, int $userId): void
    {
        $this->notifications->markAllRead($userType, $userId);
    }

    /**
     * Best-effort email send alongside an in-app notification — used
     * for the handful of events worth emailing about (order placed,
     * shipped, delivered). Never throws: a failed email should not
     * break the calling flow (checkout, status update, etc.).
     */
    public function emailAlso(string $to, string $subject, string $htmlBody): void
    {
        try {
            ProviderFactory::email()->send($to, $subject, $htmlBody);
        } catch (\Exception $e) {
            error_log('Notification email failed: ' . $e->getMessage());
        }
    }

    /** Same idea for SMS — used sparingly (e.g. OTP, shipment out for delivery). */
    public function smsAlso(string $phone, string $message): void
    {
        try {
            ProviderFactory::sms()->send($phone, $message);
        } catch (\Exception $e) {
            error_log('Notification SMS failed: ' . $e->getMessage());
        }
    }
}
