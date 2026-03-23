<?php

namespace App\Services;

use App\Contracts\InvoiceRepositoryInterface;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository
    ) {}

    public function getKpis(): array
    {
        return $this->invoiceRepository->getKpis();
    }

    public function getOverdueInvoices(): Collection
    {
        return $this->invoiceRepository->getOverdue();
    }

    public function getRecentInvoices(int $limit = 10): Collection
    {
        return Invoice::with('vendor')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getMonthlyTotals(): array
    {
        $results = Invoice::select(
                DB::raw("DATE_FORMAT(issued_at, '%Y-%m') as month"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('issued_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $results->map(fn ($row) => [
            'month' => $row->month,
            'total' => (float) $row->total,
            'count' => (int) $row->count,
        ])->toArray();
    }
}
