<?php
// api/Controllers/PingController.php
namespace App\Api\Controllers;

use App\Core\Controller;

class PingController extends Controller
{
    public function ping(): void
    {
        $this->json(['status' => 'ok']);
    }
}
