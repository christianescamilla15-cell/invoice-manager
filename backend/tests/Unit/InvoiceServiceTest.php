<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\Vendor;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = app(InvoiceService::class);
    }

    // ── generateInvoiceNumber ─────────────────────────────────────

    public function test_generate_invoice_number_has_correct_format(): void
    {
        $number = $this->invoiceService->generateInvoiceNumber();
        $year = now()->year;

        $this->assertMatchesRegularExpression("/^INV-{$year}-\d{4}$/", $number);
    }

    public function test_generate_invoice_number_starts_at_0001(): void
    {
        $number = $this->invoiceService->generateInvoiceNumber();
        $year = now()->year;

        $this->assertEquals("INV-{$year}-0001", $number);
    }

    public function test_generate_invoice_number_increments(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $year = now()->year;

        Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'invoice_number' => "INV-{$year}-0005",
        ]);

        $number = $this->invoiceService->generateInvoiceNumber();

        $this->assertEquals("INV-{$year}-0006", $number);
    }

    // ── recalculateTotals ─────────────────────────────────────────

    public function test_recalculate_totals_with_iva(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'tax_type' => 'iva',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 2,
            'unit_price' => 5000,
            'amount' => 10000,
        ]);

        $updated = $this->invoiceService->recalculateTotals($invoice);

        $this->assertEquals('10000.00', $updated->subtotal);
        $this->assertEquals('1600.00', $updated->tax_amount);
        $this->assertEquals('0.00', $updated->retention_amount);
        $this->assertEquals('11600.00', $updated->total);
    }

    public function test_recalculate_totals_with_iva_retention(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'tax_type' => 'iva_retention',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 1,
            'unit_price' => 10000,
            'amount' => 10000,
        ]);

        $updated = $this->invoiceService->recalculateTotals($invoice);

        $this->assertEquals('10000.00', $updated->subtotal);
        $this->assertEquals('1600.00', $updated->tax_amount);
        $this->assertEquals('1066.70', $updated->retention_amount);
        $this->assertEquals('10533.30', $updated->total);
    }

    public function test_recalculate_totals_with_exempt(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'tax_type' => 'exempt',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 5,
            'unit_price' => 200,
            'amount' => 1000,
        ]);

        $updated = $this->invoiceService->recalculateTotals($invoice);

        $this->assertEquals('1000.00', $updated->subtotal);
        $this->assertEquals('0.00', $updated->tax_amount);
        $this->assertEquals('0.00', $updated->retention_amount);
        $this->assertEquals('1000.00', $updated->total);
    }

    public function test_recalculate_totals_sums_multiple_items(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'tax_type' => 'iva',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 2,
            'unit_price' => 1000,
            'amount' => 2000,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 3,
            'unit_price' => 500,
            'amount' => 1500,
        ]);

        $updated = $this->invoiceService->recalculateTotals($invoice);

        // subtotal = 2000 + 1500 = 3500
        $this->assertEquals('3500.00', $updated->subtotal);
        $this->assertEquals('560.00', $updated->tax_amount); // 3500 * 0.16
        $this->assertEquals('0.00', $updated->retention_amount);
        $this->assertEquals('4060.00', $updated->total); // 3500 + 560
    }

    // ── Status transitions ────────────────────────────────────────

    public function test_draft_can_transition_to_pending(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => 'draft',
        ]);

        $updated = $this->invoiceService->updateStatus($invoice->id, 'pending');

        $this->assertEquals('pending', $updated->status);
    }

    public function test_draft_can_transition_to_cancelled(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => 'draft',
        ]);

        $updated = $this->invoiceService->updateStatus($invoice->id, 'cancelled');

        $this->assertEquals('cancelled', $updated->status);
    }

    public function test_pending_can_transition_to_paid(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ]);

        $updated = $this->invoiceService->updateStatus($invoice->id, 'paid');

        $this->assertEquals('paid', $updated->status);
        $this->assertNotNull($updated->paid_at);
    }

    public function test_paid_cannot_transition_to_draft(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => 'paid',
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->invoiceService->updateStatus($invoice->id, 'draft');
    }

    public function test_paid_cannot_transition_to_pending(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => 'paid',
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->invoiceService->updateStatus($invoice->id, 'pending');
    }

    public function test_cancelled_can_transition_to_draft(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => 'cancelled',
        ]);

        $updated = $this->invoiceService->updateStatus($invoice->id, 'draft');

        $this->assertEquals('draft', $updated->status);
    }

    public function test_overdue_can_transition_to_paid(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => 'overdue',
        ]);

        $updated = $this->invoiceService->updateStatus($invoice->id, 'paid');

        $this->assertEquals('paid', $updated->status);
        $this->assertNotNull($updated->paid_at);
    }

    public function test_overdue_cannot_transition_to_draft(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => 'overdue',
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->invoiceService->updateStatus($invoice->id, 'draft');
    }
}
