<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ── List ──────────────────────────────────────────────────────

    public function test_can_list_vendors(): void
    {
        Vendor::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/vendors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'rfc', 'business_name', 'email', 'created_at'],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_user_cannot_list_vendors(): void
    {
        $response = $this->getJson('/api/vendors');
        $response->assertStatus(401);
    }

    // ── Create ────────────────────────────────────────────────────

    public function test_can_create_vendor(): void
    {
        $payload = [
            'rfc' => 'ABC123456XY9',
            'business_name' => 'Acme Corp',
            'contact_name' => 'John Doe',
            'email' => 'john@acme.com',
            'phone' => '5551234567',
            'address' => 'Calle Falsa 123',
            'city' => 'CDMX',
            'state' => 'Ciudad de México',
            'zip_code' => '06600',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/vendors', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.business_name', 'Acme Corp')
            ->assertJsonPath('message', 'Proveedor creado correctamente.');

        $this->assertDatabaseHas('vendors', [
            'rfc' => 'ABC123456XY9',
            'business_name' => 'Acme Corp',
        ]);
    }

    public function test_cannot_create_vendor_without_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/vendors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rfc', 'business_name']);
    }

    public function test_cannot_create_vendor_with_duplicate_rfc(): void
    {
        Vendor::factory()->create(['rfc' => 'DUP123456AB1']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/vendors', [
                'rfc' => 'DUP123456AB1',
                'business_name' => 'Duplicate Corp',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rfc']);
    }

    // ── Update ────────────────────────────────────────────────────

    public function test_can_update_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/vendors/{$vendor->id}", [
                'business_name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.business_name', 'Updated Name')
            ->assertJsonPath('message', 'Proveedor actualizado correctamente.');

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'business_name' => 'Updated Name',
        ]);
    }

    // ── Delete ────────────────────────────────────────────────────

    public function test_can_delete_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/vendors/{$vendor->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Proveedor eliminado correctamente.');

        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    // ── RFC uppercase ─────────────────────────────────────────────

    public function test_rfc_is_stored_uppercase(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/vendors', [
                'rfc' => 'abc123456xy9',
                'business_name' => 'Lowercase RFC Corp',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('vendors', [
            'rfc' => 'ABC123456XY9',
        ]);
    }

    // ── Show ──────────────────────────────────────────────────────

    public function test_can_show_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/vendors/{$vendor->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $vendor->id)
            ->assertJsonStructure([
                'data' => ['id', 'rfc', 'business_name', 'email'],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_vendor(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/vendors/99999');

        $response->assertStatus(404);
    }
}
