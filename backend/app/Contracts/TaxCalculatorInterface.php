<?php

namespace App\Contracts;

interface TaxCalculatorInterface
{
    /**
     * Calculate tax, retention, and total from subtotal.
     *
     * @return array{tax: float, retention: float, total: float}
     */
    public function calculate(float $subtotal): array;
}
