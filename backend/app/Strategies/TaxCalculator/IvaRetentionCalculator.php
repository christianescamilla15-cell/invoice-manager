<?php

namespace App\Strategies\TaxCalculator;

use App\Contracts\TaxCalculatorInterface;

class IvaRetentionCalculator implements TaxCalculatorInterface
{
    public function calculate(float $subtotal): array
    {
        $tax = round($subtotal * 0.16, 2);
        $retention = round($subtotal * 0.10667, 2);

        return [
            'tax' => $tax,
            'retention' => $retention,
            'total' => round($subtotal + $tax - $retention, 2),
        ];
    }
}
