<?php
// api/Controllers/AuthController.php
namespace App\Api\Controllers;

use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Services\JwtService;
use App\Repositories\UserRepository;

/**
 * Issues a JWT for api/ bearer-token auth (see App\Core\Middleware::
 * apiAuth()) — completely separate from the session-cookie login the
 * server-rendered customer/seller dashboards use (App\Core\Auth).
 * This is the auth seam for the future React frontend mentioned in
 * api/Routes/routes.php; nothing here touches $_SESSION.
 */
class AuthController extends Controller
{
    public function login(): void
    {
        $body  = $this->jsonBody();
        $email = trim((string) ($body['email'] ?? ''));
        $password = (string) ($body['password'] ?? '');
        $role  = ($body['role'] ?? 'customer') === 'seller' ? 'seller' : 'customer';

        if ($email === '' || $password === '') {
            $this->json(['success' => false, 'message' => 'Email and password are required.'], 422);
            return;
        }

        $rlKey = RateLimiter::keyFor('api_login', $email);
        if (RateLimiter::tooManyAttempts($rlKey, 10, 15)) {
            $this->json(['success' => false, 'message' => 'Too many login attempts. Try again in ' . RateLimiter::availableInMinutes($rlKey) . ' minute(s).'], 429);
            return;
        }

        $user = (new UserRepository())->findByEmail($email);
        if (!$user || $user['role'] !== $role || !password_verify($password, $user['password'])) {
            RateLimiter::hit($rlKey, 10, 15);
            $this->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
            return;
        }
        if (!(int) $user['is_active']) {
            $this->json(['success' => false, 'message' => 'Account has been deactivated.'], 403);
            return;
        }
        if (!(int) $user['is_verified']) {
            $this->json(['success' => false, 'message' => 'Please verify your email before signing in.'], 403);
            return;
        }

        RateLimiter::clear($rlKey);
        $token = JwtService::encode([
            'sub'   => (int) $user['id'],
            'role'  => $user['role'],
            'email' => $user['email'],
            'name'  => $user['name'],
        ]);

        $this->json([
            'success'    => true,
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWT_TTL,
        ]);
    }

    private function jsonBody(): array
    {
        // Accepts both a JSON body (typical for an API client) and a
        // regular form post, so this also works from a plain HTML form
        // or curl -d without extra client-side setup.
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : $_POST;
    }
}
