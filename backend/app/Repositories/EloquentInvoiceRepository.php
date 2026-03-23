<?php

namespace App\Repositories;

use App\Contracts\InvoiceRepositoryInterface;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Invoice::with(['vendor', 'items']);

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->byVendor($filters['vendor_id']);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->byDateRange($filters['from'], $filters['to']);
        }

        return $query->orderByDesc('issued_at')->paginate(15);
    }

    public function findById(int $id): Invoice
    {
        return Invoice::with(['vendor', 'items', 'user'])->findOrFail($id);
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function update(int $id, array $data): Invoice
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update($data);

        return $invoice->fresh(['vendor', 'items']);
    }

    public function delete(int $id): bool
    {
        $invoice = Invoice::findOrFail($id);

        return $invoice->delete();
    }

    public function getOverdue(): Collection
    {
        return Invoice::with('vendor')
            ->overdue()
            ->orderBy('due_at')
            ->get();
    }

    public function getKpis(): array
    {
        $invoices = Invoice::query();

        return [
            'total_invoices' => (clone $invoices)->count(),
            'total_pending' => (clone $invoices)->where('status', 'pending')->count(),
            'total_overdue' => (clone $invoices)->where('status', 'pending')->where('due_at', '<', now())->count(),
            'total_paid' => (clone $invoices)->where('status', 'paid')->count(),
            'total_amount' => (float) (clone $invoices)->sum('total'),
            'total_pending_amount' => (float) (clone $invoices)->where('status', 'pending')->sum('total'),
            'total_overdue_amount' => (float) (clone $invoices)->where('status', 'pending')->where('due_at', '<', now())->sum('total'),
        ];
    }
}
