<?php
namespace App\Admin\Services;

use App\Repositories\ReportRepository;

class ReportService
{
    private ReportRepository $reports;

    public function __construct()
    {
        $this->reports = new ReportRepository();
    }

    public function sales(string $from, string $to): array
    {
        return [
            'data'    => $this->reports->salesByDate($from, $to),
            'summary' => $this->reports->salesSummary($from, $to),
        ];
    }

    public function commission(string $from, string $to): array
    {
        return $this->reports->commissionBySeller($from, $to);
    }

    public function sellers(): array
    {
        return $this->reports->sellerPerformance();
    }
}
