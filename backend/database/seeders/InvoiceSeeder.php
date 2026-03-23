<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\Vendor;
use App\Strategies\TaxCalculatorFactory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $vendors = Vendor::all();

        $descriptions = [
            'Resma de papel bond tamaño carta',
            'Tóner HP LaserJet Pro M404',
            'Cartuchos de tinta Epson L3250',
            'Servicio de mantenimiento de aires acondicionados',
            'Licencia anual Microsoft 365 Business',
            'Licencia Adobe Creative Cloud',
            'Servicio de limpieza profunda oficinas',
            'Consultoría fiscal trimestral',
            'Desarrollo de módulo de facturación',
            'Hosting dedicado 12 meses',
            'Dominio .com.mx renovación anual',
            'Certificado SSL Wildcard',
            'Sillas ergonómicas para oficina',
            'Escritorios ejecutivos',
            'Servicio de contabilidad mensual',
            'Mantenimiento preventivo servidores',
            'Cableado estructurado Cat 6',
            'Servicio de diseño de logotipo',
            'Impresión de papelería corporativa (millar)',
            'Material de empaque cajas corrugadas',
            'Servicio de mensajería express',
            'Renta mensual de multifuncional',
            'Curso de capacitación Excel avanzado',
            'Servicio de nómina mensual',
            'Mantenimiento de planta eléctrica',
            'Suministro de agua purificada',
            'Servicio de fumigación trimestral',
            'Póliza de seguro equipo de cómputo',
            'Actualización de sistema ERP',
            'Soporte técnico remoto mensual',
        ];

        $statuses = ['draft', 'pending', 'paid', 'overdue', 'cancelled'];
        $taxTypes = ['iva', 'iva_retention', 'exempt'];
        $invoiceCounter = 1;

        for ($i = 0; $i < 50; $i++) {
            $issuedAt = Carbon::now()->subDays(rand(1, 180));
            $dueAt = (clone $issuedAt)->addDays(rand(15, 60));

            $status = $statuses[array_rand($statuses)];

            // Make overdue invoices realistic: pending + past due
            if ($status === 'overdue') {
                $status = 'pending';
                $dueAt = Carbon::now()->subDays(rand(1, 30));
            }

            $taxType = $taxTypes[array_rand($taxTypes)];
            $vendor = $vendors->random();

            $invoiceNumber = 'INV-' . $issuedAt->format('Y') . '-' . str_pad($invoiceCounter, 4, '0', STR_PAD_LEFT);
            $invoiceCounter++;

            $invoice = Invoice::create([
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'invoice_number' => $invoiceNumber,
                'status' => $status,
                'tax_type' => $taxType,
                'issued_at' => $issuedAt,
                'due_at' => $dueAt,
                'notes' => rand(0, 10) > 7 ? 'Favor de realizar transferencia a cuenta CLABE proporcionada.' : null,
                'paid_at' => $status === 'paid' ? $dueAt->copy()->subDays(rand(0, 10)) : null,
            ]);

            // Create 2-5 items per invoice
            $numItems = rand(2, 5);
            $subtotal = 0;

            for ($j = 0; $j < $numItems; $j++) {
                $quantity = round(rand(1, 20) + (rand(0, 1) ? rand(1, 99) / 100 : 0), 2);
                $unitPrice = round(rand(150, 25000) + rand(0, 99) / 100, 2);
                $amount = round($quantity * $unitPrice, 2);
                $subtotal += $amount;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $descriptions[array_rand($descriptions)],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'amount' => $amount,
                ]);
            }

            // Recalculate totals using tax strategy
            $calculator = TaxCalculatorFactory::make($taxType);
            $taxes = $calculator->calculate($subtotal);

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxes['tax'],
                'retention_amount' => $taxes['retention'],
                'total' => $taxes['total'],
            ]);
        }
    }
}
