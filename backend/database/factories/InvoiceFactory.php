<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $issuedAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $dueAt = (clone $issuedAt)->modify('+' . $this->faker->numberBetween(15, 60) . ' days');

        return [
            'user_id' => User::factory(),
            'vendor_id' => Vendor::factory(),
            'invoice_number' => 'INV-' . $issuedAt->format('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => $this->faker->randomElement(['draft', 'pending', 'paid', 'overdue', 'cancelled']),
            'tax_type' => $this->faker->randomElement(['iva', 'iva_retention', 'exempt']),
            'issued_at' => $issuedAt,
            'due_at' => $dueAt,
            'subtotal' => 0,
            'tax_amount' => 0,
            'retention_amount' => 0,
            'total' => 0,
            'notes' => $this->faker->optional(0.3)->sentence(),
            'paid_at' => null,
        ];
    }
}
