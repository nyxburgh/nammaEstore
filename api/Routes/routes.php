<?php
// api/Routes/routes.php
//
// REST API seam for the future React frontend. Empty scaffold for
// now — add App\Api\Controllers\* classes and wire routes here as
// screens get converted. Autoload mapping lives in
// app/Core/Autoloader.php ('App\Api\Controllers\' => 'api/Controllers/').

use App\Core\Router;
use App\Api\Controllers\{PingController, AuthController, MeController};

$router = new Router();

$router->get('/api/ping', PingController::class . '@ping');

// ── JWT bearer-token auth (see App\Core\Middleware::apiAuth()) ──────
// Separate from the session-cookie login the server-rendered
// dashboards use — this is the seam for the future React frontend.
$router->post('/api/auth/login', AuthController::class . '@login');
$router->get('/api/me',          MeController::class . '@show');

return $router;
