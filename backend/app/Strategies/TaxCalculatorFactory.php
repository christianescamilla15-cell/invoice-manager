<?php

namespace App\Strategies;

use App\Contracts\TaxCalculatorInterface;
use App\Strategies\TaxCalculator\ExemptTaxCalculator;
use App\Strategies\TaxCalculator\IvaRetentionCalculator;
use App\Strategies\TaxCalculator\IvaTaxCalculator;
use InvalidArgumentException;

class TaxCalculatorFactory
{
    public static function make(string $taxType): TaxCalculatorInterface
    {
        return match ($taxType) {
            'iva' => new IvaTaxCalculator(),
            'iva_retention' => new IvaRetentionCalculator(),
            'exempt' => new ExemptTaxCalculator(),
            default => throw new InvalidArgumentException("Unknown tax type: {$taxType}"),
        };
    }
}
