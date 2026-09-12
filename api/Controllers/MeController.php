<?php
// api/Controllers/MeController.php
namespace App\Api\Controllers;

use App\Core\{Controller, Middleware};
use App\Repositories\UserRepository;

/**
 * Reference implementation of a JWT-protected api/ endpoint — shows
 * the pattern (Middleware::apiAuth() first, then use the returned
 * claims) that future React-frontend endpoints under api/ should
 * follow for both the customer and seller dashboards.
 */
class MeController extends Controller
{
    public function show(): void
    {
        $claims = Middleware::apiAuth();

        $user = (new UserRepository())->findById((int) $claims['sub']);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }

        $this->json([
            'success' => true,
            'user'    => [
                'id'    => (int) $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ]);
    }
}
