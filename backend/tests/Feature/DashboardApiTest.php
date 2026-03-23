<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
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

    public function test_can_get_dashboard_kpis(): void
    {
        // Create invoices with different statuses
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'paid',
            'total' => 10000,
        ]);

        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'pending',
            'total' => 5000,
            'due_at' => now()->addDays(30),
        ]);

        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'pending',
            'total' => 3000,
            'due_at' => now()->subDays(5), // overdue
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'kpis' => [
                    'total_invoices',
                    'total_pending',
                    'total_overdue',
                    'total_paid',
                    'total_amount',
                    'total_pending_amount',
                    'total_overdue_amount',
                ],
                'recent_invoices',
                'monthly_totals',
            ]);

        $kpis = $response->json('kpis');
        $this->assertEquals(3, $kpis['total_invoices']);
        $this->assertEquals(2, $kpis['total_pending']);
        $this->assertEquals(1, $kpis['total_overdue']);
        $this->assertEquals(1, $kpis['total_paid']);
    }

    public function test_can_get_overdue_invoices(): void
    {
        // Overdue: status=pending and due_at < now
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'pending',
            'due_at' => now()->subDays(10),
        ]);

        // Not overdue: status=pending but due_at in future
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'pending',
            'due_at' => now()->addDays(30),
        ]);

        // Not overdue: status=paid
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'paid',
            'due_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard/overdue');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'invoice_number', 'status', 'total'],
                ],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }

    public function test_dashboard_with_no_invoices(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200);

        $kpis = $response->json('kpis');
        $this->assertEquals(0, $kpis['total_invoices']);
        $this->assertEquals(0, $kpis['total_pending']);
        $this->assertEquals(0, $kpis['total_overdue']);
        $this->assertEquals(0, $kpis['total_paid']);
    }
}
