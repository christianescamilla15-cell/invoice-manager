<?php

namespace App\Services;

use App\Contracts\InvoiceRepositoryInterface;
use App\Models\Invoice;
use App\Strategies\TaxCalculatorFactory;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository
    ) {}

    public function createInvoice(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
            $invoice = $this->invoiceRepository->create($data);

            foreach ($items as $item) {
                $item['amount'] = round($item['quantity'] * $item['unit_price'], 2);
                $invoice->items()->create($item);
            }

            return $this->recalculateTotals($invoice);
        });
    }

    public function updateInvoice(int $id, array $data, ?array $items = null): Invoice
    {
        return DB::transaction(function () use ($id, $data, $items) {
            $invoice = $this->invoiceRepository->update($id, $data);

            if ($items !== null) {
                $invoice->items()->delete();

                foreach ($items as $item) {
                    $item['amount'] = round($item['quantity'] * $item['unit_price'], 2);
                    $invoice->items()->create($item);
                }
            }

            return $this->recalculateTotals($invoice);
        });
    }

    public function deleteInvoice(int $id): bool
    {
        return $this->invoiceRepository->delete($id);
    }

    public function updateStatus(int $id, string $status): Invoice
    {
        $invoice = $this->invoiceRepository->findById($id);

        $allowedTransitions = [
            'draft' => ['pending', 'cancelled'],
            'pending' => ['paid', 'overdue', 'cancelled'],
            'overdue' => ['paid', 'cancelled'],
            'paid' => [],
            'cancelled' => ['draft'],
        ];

        $currentStatus = $invoice->status;
        $allowed = $allowedTransitions[$currentStatus] ?? [];

        if (!in_array($status, $allowed)) {
            abort(422, "Cannot transition from '{$currentStatus}' to '{$status}'.");
        }

        $updateData = ['status' => $status];

        if ($status === 'paid') {
            $updateData['paid_at'] = now();
        }

        return $this->invoiceRepository->update($id, $updateData);
    }

    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $invoice->load('items');

        $subtotal = $invoice->items->sum(fn ($item) => (float) $item->amount);
        $calculator = TaxCalculatorFactory::make($invoice->tax_type);
        $taxes = $calculator->calculate($subtotal);

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxes['tax'],
            'retention_amount' => $taxes['retention'],
            'total' => $taxes['total'],
        ]);

        return $invoice->fresh(['vendor', 'items']);
    }

    public function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";

        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('invoice_number')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) str_replace($prefix, '', $lastInvoice->invoice_number);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
