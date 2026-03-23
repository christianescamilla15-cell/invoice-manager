<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $descriptions = [
            'Resma de papel bond carta',
            'Tóner para impresora HP LaserJet',
            'Servicio de mantenimiento preventivo',
            'Licencia anual Microsoft 365',
            'Servicio de limpieza mensual',
            'Consultoría en sistemas',
            'Desarrollo de software a medida',
            'Hosting y dominio anual',
            'Papelería y artículos de oficina',
            'Servicio de contabilidad mensual',
            'Mantenimiento de equipo de cómputo',
            'Servicio de diseño gráfico',
            'Material de empaque y embalaje',
            'Renta de equipo de proyección',
            'Capacitación en seguridad laboral',
        ];

        $quantity = $this->faker->randomFloat(2, 1, 50);
        $unitPrice = $this->faker->randomFloat(2, 100, 15000);

        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->randomElement($descriptions),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => round($quantity * $unitPrice, 2),
        ];
    }
}
