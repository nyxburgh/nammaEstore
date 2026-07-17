<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\ReportService;

class ReportController extends AdminController
{
    private ReportService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new ReportService();
    }

    public function index(): void { Middleware::can('reports'); $this->view('reports.index',['title'=>'Reports']); }

    public function sales(): void {
        Middleware::can('reports');
        $from = $this->input('date_from', date('Y-m-01'));
        $to   = $this->input('date_to',   date('Y-m-d'));
        $r = $this->service->sales($from, $to);
        $this->view('reports.sales', ['data'=>$r['data'],'summary'=>$r['summary'],'from'=>$from,'to'=>$to,'title'=>'Sales Report']);
    }

    public function commission(): void {
        Middleware::can('reports');
        $from = $this->input('date_from', date('Y-m-01'));
        $to   = $this->input('date_to',   date('Y-m-d'));
        $data = $this->service->commission($from, $to);
        $this->view('reports.commission', ['data'=>$data,'from'=>$from,'to'=>$to,'title'=>'Commission Report']);
    }

    public function vendors(): void {
        Middleware::can('reports');
        $data = $this->service->vendors();
        $this->view('reports.vendors', ['data'=>$data,'title'=>'Vendor Report']);
    }
}
