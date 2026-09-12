<?php
namespace App\Core\Services;

/**
 * Minimal, dependency-free HS256 JWT encode/decode for api/ (the REST
 * seam for the future React frontend — see api/Routes/routes.php).
 * Hand-rolled rather than pulling in firebase/php-jwt: this app has no
 * `vendor/` yet (composer install has never been run here — see
 * SmtpEmailProvider/InvoicePdfService, which both degrade gracefully
 * for the same reason), and HS256 is simple enough to implement
 * correctly in ~40 lines without a dependency. Swap this out for
 * firebase/php-jwt if/when composer packages are set up.
 *
 * This is intentionally separate from the session-cookie auth that
 * still protects the server-rendered admin/seller/customer dashboards
 * (App\Core\Auth) — that path is untouched.
 */
class JwtService
{
    /** @param array $claims Arbitrary claims (sub, role, email, ...) — exp/iat are added automatically. */
    public static function encode(array $claims, ?int $ttlSeconds = null): string
    {
        $ttl = $ttlSeconds ?? JWT_TTL;
        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = self::base64UrlEncode(json_encode($claims + [
            'iat' => time(),
            'exp' => time() + $ttl,
        ]));
        $signature = self::sign("{$header}.{$payload}");
        return "{$header}.{$payload}.{$signature}";
    }

    /** Returns the decoded claims, or null if the token is malformed, tampered with, or expired. */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $payload, $signature] = $parts;

        if (!hash_equals(self::sign("{$header}.{$payload}"), $signature)) return null;

        $claims = json_decode(self::base64UrlDecode($payload), true);
        if (!is_array($claims)) return null;
        if (!isset($claims['exp']) || time() >= (int) $claims['exp']) return null;

        return $claims;
    }

    private static function sign(string $data): string
    {
        return self::base64UrlEncode(hash_hmac('sha256', $data, JWT_SECRET, true));
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
