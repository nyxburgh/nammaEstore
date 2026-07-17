<?php
namespace App\Admin\Controllers;
use App\Core\{Auth, Middleware};
use App\Admin\Services\{SettlementService, WithdrawalService};

class SettlementController extends AdminController
{
    private SettlementService $settlement;
    private WithdrawalService $withdrawals;

    public function __construct() {
        parent::__construct();
        $this->settlement  = new SettlementService();
        $this->withdrawals = new WithdrawalService();
    }

    public function index(): void
    {
        Middleware::adminAuth();
        $this->view('settlements.index', [
            'title'        => 'Settlement Engine',
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    /** Trigger a manual eligibility pass — same routine a cron would call. */
    public function runPass(): void
    {
        Middleware::superAdmin();
        csrf_check();
        $result = $this->settlement->runEligibilityPass();
        $this->setFlash('success', "Evaluated {$result['evaluated']} — {$result['eligible']} eligible, {$result['held']} on hold, {$result['not_yet']} not yet due.");
        $this->redirect(ADMIN_URL . '/settlements');
    }

    /** Move eligible settlements into vendor wallets. */
    public function credit(): void
    {
        Middleware::superAdmin();
        csrf_check();
        $result = $this->settlement->creditEligibleToWallets();
        $this->setFlash('success', "Credited {$result['credited']} settlement(s), total ₹" . number_format($result['total_amount'], 2));
        $this->redirect(ADMIN_URL . '/settlements');
    }

    // ── Withdrawal queue ─────────────────────────────────────
    public function withdrawals(): void
    {
        Middleware::adminAuth();
        $f = ['status' => $this->input('status', '')];
        $this->view('settlements.withdrawals', [
            'title'        => 'Withdrawal Requests',
            'withdrawals'  => $this->withdrawals->queue((int) $this->input('page', 1), $f),
            'filters'      => $f,
            'sidebarStats' => $this->sidebarStats(),
        ]);
    }

    public function approveWithdrawal(string $id): void
    {
        Middleware::superAdmin();
        csrf_check();
        $r = $this->withdrawals->approve((int) $id, Auth::adminId());
        $this->json($r);
    }

    public function rejectWithdrawal(string $id): void
    {
        Middleware::superAdmin();
        csrf_check();
        $r = $this->withdrawals->reject((int) $id, Auth::adminId(), $this->input('note', ''));
        $this->json($r);
    }

    public function markWithdrawalPaid(string $id): void
    {
        Middleware::superAdmin();
        csrf_check();
        $r = $this->withdrawals->markPaid((int) $id, Auth::adminId());
        $this->json($r);
    }
}
