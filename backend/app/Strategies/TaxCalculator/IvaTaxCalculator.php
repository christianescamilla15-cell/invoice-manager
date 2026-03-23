<?php

namespace App\Strategies\TaxCalculator;

use App\Contracts\TaxCalculatorInterface;

class IvaTaxCalculator implements TaxCalculatorInterface
{
    public function calculate(float $subtotal): array
    {
        $tax = round($subtotal * 0.16, 2);

        return [
            'tax' => $tax,
            'retention' => 0.00,
            'total' => round($subtotal + $tax, 2),
        ];
    }
}
