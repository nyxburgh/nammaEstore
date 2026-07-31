<?php
// api/Routes/routes.php
//
// REST API seam for the future React frontend. Empty scaffold for
// now — add App\Api\Controllers\* classes and wire routes here as
// screens get converted. Autoload mapping lives in
// app/Core/Autoloader.php ('App\Api\Controllers\' => 'api/Controllers/').

use App\Core\Router;
use App\Api\Controllers\PingController;

$router = new Router();

$router->get('/api/ping', PingController::class . '@ping');

return $router;
