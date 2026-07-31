<?php
namespace App\Frontend\Controllers;
use App\Core\Controller;
abstract class FrontendController extends Controller
{
    public function __construct() { $this->viewsPath = FRONTEND_VIEWS; }
}
