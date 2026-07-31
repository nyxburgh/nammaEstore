<?php
// app/admin/Controllers/AdminController.php
// Base controller for ALL admin panel controllers.
// Sets the views path to app/admin/Views/ so that
// $this->view('dashboard.index') resolves correctly.

namespace App\Admin\Controllers;

use App\Core\Controller;
use App\Repositories\SidebarRepository;

abstract class AdminController extends Controller
{
    public function __construct()
    {
        $this->viewsPath = ADMIN_VIEWS;
    }

    /**
     * Sidebar counts — passed as $sidebarStats to every view via layout.
     * Called by controllers that need it; layout uses $sidebarStats ?? [].
     */
    protected function sidebarStats(): array
    {
        return (new SidebarRepository())->getAdminCounts();
    }
}
