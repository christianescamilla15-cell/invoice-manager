<?php

namespace App\Strategies\TaxCalculator;

use App\Contracts\TaxCalculatorInterface;

class ExemptTaxCalculator implements TaxCalculatorInterface
{
    public function calculate(float $subtotal): array
    {
        return [
            'tax' => 0.00,
            'retention' => 0.00,
            'total' => round($subtotal, 2),
        ];
    }
}
