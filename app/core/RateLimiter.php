<?php
namespace App\Core;

/**
 * Generic rate limiter backed by mc_rate_limits. DB-backed rather
 * than session-based because a session is trivially reset by an
 * attacker (new cookie, incognito tab) — the limit needs to persist
 * against the actual key being throttled (IP+email, IP alone, etc.)
 * regardless of session state.
 */
class RateLimiter
{
    /**
     * Records an attempt against $key and returns whether it's still
     * allowed. Once $maxAttempts is reached within $decayMinutes, the
     * key is blocked for $decayMinutes from the last attempt.
     */
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $db  = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM `" . DB_PREFIX . "rate_limits` WHERE rate_key=?", [$key]);

        if (!$row) return false;

        if ($row['blocked_until'] && strtotime($row['blocked_until']) > time()) {
            return true;
        }

        // Blocked window has expired — but keep counting only within
        // the current decay window from the first attempt.
        $windowExpired = strtotime($row['first_attempt_at']) < (time() - $decayMinutes * 60);
        if ($windowExpired) return false;

        return (int) $row['attempts'] >= $maxAttempts;
    }

    public static function hit(string $key, int $maxAttempts, int $decayMinutes): void
    {
        $db  = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM `" . DB_PREFIX . "rate_limits` WHERE rate_key=?", [$key]);

        if (!$row) {
            $db->insert(
                "INSERT INTO `" . DB_PREFIX . "rate_limits` (rate_key, attempts, first_attempt_at) VALUES (?,1,NOW())",
                [$key]
            );
            return;
        }

        $windowExpired = strtotime($row['first_attempt_at']) < (time() - $decayMinutes * 60);
        if ($windowExpired) {
            $db->execute(
                "UPDATE `" . DB_PREFIX . "rate_limits` SET attempts=1, first_attempt_at=NOW(), blocked_until=NULL WHERE rate_key=?",
                [$key]
            );
            return;
        }

        $attempts = (int) $row['attempts'] + 1;
        $blockedUntil = $attempts >= $maxAttempts
            ? date('Y-m-d H:i:s', time() + $decayMinutes * 60)
            : null;

        $db->execute(
            "UPDATE `" . DB_PREFIX . "rate_limits` SET attempts=?, blocked_until=? WHERE rate_key=?",
            [$attempts, $blockedUntil, $key]
        );
    }

    public static function clear(string $key): void
    {
        Database::getInstance()->execute("DELETE FROM `" . DB_PREFIX . "rate_limits` WHERE rate_key=?", [$key]);
    }

    /** Minutes remaining until $key is unblocked, or 0 if not blocked. */
    public static function availableInMinutes(string $key): int
    {
        $row = Database::getInstance()->fetchOne("SELECT blocked_until FROM `" . DB_PREFIX . "rate_limits` WHERE rate_key=?", [$key]);
        if (!$row || !$row['blocked_until']) return 0;
        $seconds = strtotime($row['blocked_until']) - time();
        return $seconds > 0 ? (int) ceil($seconds / 60) : 0;
    }

    /** Convenience: a key scoped to the requester's IP + an identifier (email, route, etc.) */
    public static function keyFor(string $action, string $identifier): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return $action . ':' . $ip . ':' . strtolower($identifier);
    }
}
