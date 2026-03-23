<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->vendor = Vendor::factory()->create();
    }

    // ── List ──────────────────────────────────────────────────────

    public function test_can_list_invoices(): void
    {
        Invoice::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'invoice_number', 'status', 'total', 'vendor'],
                ],
            ]);
    }

    // ── Create ────────────────────────────────────────────────────

    public function test_can_create_invoice_with_items(): void
    {
        $payload = [
            'vendor_id' => $this->vendor->id,
            'tax_type' => 'iva',
            'issued_at' => '2026-03-01',
            'due_at' => '2026-03-31',
            'notes' => 'Test invoice',
            'items' => [
                [
                    'description' => 'Service A',
                    'quantity' => 2,
                    'unit_price' => 5000,
                ],
                [
                    'description' => 'Service B',
                    'quantity' => 1,
                    'unit_price' => 3000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/invoices', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Factura creada correctamente.')
            ->assertJsonStructure([
                'data' => ['id', 'invoice_number', 'status', 'subtotal', 'tax_amount', 'total', 'items'],
            ]);

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('invoice_items', 2);
    }

    public function test_invoice_totals_are_calculated_correctly(): void
    {
        $payload = [
            'vendor_id' => $this->vendor->id,
            'tax_type' => 'iva',
            'issued_at' => '2026-03-01',
            'due_at' => '2026-03-31',
            'items' => [
                [
                    'description' => 'Item 1',
                    'quantity' => 2,
                    'unit_price' => 5000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/invoices', $payload);

        $response->assertStatus(201);

        // subtotal = 2 * 5000 = 10000
        // tax = 10000 * 0.16 = 1600
        // total = 11600
        $response->assertJsonPath('data.subtotal', '10000.00')
            ->assertJsonPath('data.tax_amount', '1600.00')
            ->assertJsonPath('data.retention_amount', '0.00')
            ->assertJsonPath('data.total', '11600.00');
    }

    public function test_invoice_totals_with_iva_retention(): void
    {
        $payload = [
            'vendor_id' => $this->vendor->id,
            'tax_type' => 'iva_retention',
            'issued_at' => '2026-03-01',
            'due_at' => '2026-03-31',
            'items' => [
                [
                    'description' => 'Consulting',
                    'quantity' => 1,
                    'unit_price' => 10000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/invoices', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.subtotal', '10000.00')
            ->assertJsonPath('data.tax_amount', '1600.00')
            ->assertJsonPath('data.retention_amount', '1066.70')
            ->assertJsonPath('data.total', '10533.30');
    }

    // ── Filter ────────────────────────────────────────────────────

    public function test_can_filter_invoices_by_status(): void
    {
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'paid',
        ]);

        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/invoices?status=paid');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $invoice) {
            $this->assertEquals('paid', $invoice['status']);
        }
    }

    // ── Update status ─────────────────────────────────────────────

    public function test_can_update_invoice_status(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => 'pending',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('message', 'Estatus actualizado correctamente.');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'pending',
        ]);
    }

    public function test_invalid_status_transition_returns_error(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => 'draft',
            ]);

        $response->assertStatus(422);
    }

    // ── Delete ────────────────────────────────────────────────────

    public function test_can_delete_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Factura eliminada correctamente.');

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    // ── PDF ───────────────────────────────────────────────────────

    public function test_can_download_pdf(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/invoices/{$invoice->id}/pdf");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    // ── Auto-generated invoice number ─────────────────────────────

    public function test_invoice_number_is_auto_generated(): void
    {
        $payload = [
            'vendor_id' => $this->vendor->id,
            'tax_type' => 'exempt',
            'issued_at' => '2026-03-01',
            'due_at' => '2026-03-31',
            'items' => [
                [
                    'description' => 'Auto-number test',
                    'quantity' => 1,
                    'unit_price' => 100,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/invoices', $payload);

        $response->assertStatus(201);

        $invoiceNumber = $response->json('data.invoice_number');
        $year = now()->year;

        $this->assertMatchesRegularExpression("/^INV-{$year}-\d{4}$/", $invoiceNumber);
    }

    // ── Validation ────────────────────────────────────────────────

    public function test_cannot_create_invoice_without_items(): void
    {
        $payload = [
            'vendor_id' => $this->vendor->id,
            'tax_type' => 'iva',
            'issued_at' => '2026-03-01',
            'due_at' => '2026-03-31',
            'items' => [],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/invoices', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_cannot_create_invoice_with_invalid_vendor(): void
    {
        $payload = [
            'vendor_id' => 99999,
            'tax_type' => 'iva',
            'issued_at' => '2026-03-01',
            'due_at' => '2026-03-31',
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/invoices', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_id']);
    }

    // ── Show ──────────────────────────────────────────────────────

    public function test_can_show_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $invoice->id)
            ->assertJsonStructure([
                'data' => ['id', 'invoice_number', 'status', 'vendor', 'items'],
            ]);
    }

    // ── Update invoice ────────────────────────────────────────────

    public function test_can_update_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'draft',
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/invoices/{$invoice->id}", [
                'notes' => 'Updated notes',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Factura actualizada correctamente.');
    }
}
