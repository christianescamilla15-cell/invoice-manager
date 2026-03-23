<?php

namespace Tests\Unit;

use App\Strategies\TaxCalculator\ExemptTaxCalculator;
use App\Strategies\TaxCalculator\IvaRetentionCalculator;
use App\Strategies\TaxCalculator\IvaTaxCalculator;
use App\Strategies\TaxCalculatorFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TaxCalculatorTest extends TestCase
{
    // ── IvaTaxCalculator ──────────────────────────────────────────

    public function test_iva_calculator_applies_16_percent_tax(): void
    {
        $calculator = new IvaTaxCalculator();
        $result = $calculator->calculate(10000);

        $this->assertEquals(1600.00, $result['tax']);
        $this->assertEquals(0.00, $result['retention']);
        $this->assertEquals(11600.00, $result['total']);
    }

    public function test_iva_calculator_with_decimal_subtotal(): void
    {
        $calculator = new IvaTaxCalculator();
        $result = $calculator->calculate(1234.56);

        $this->assertEquals(round(1234.56 * 0.16, 2), $result['tax']);
        $this->assertEquals(0.00, $result['retention']);
        $this->assertEquals(round(1234.56 + 1234.56 * 0.16, 2), $result['total']);
    }

    public function test_iva_calculator_with_zero_subtotal(): void
    {
        $calculator = new IvaTaxCalculator();
        $result = $calculator->calculate(0);

        $this->assertEquals(0.00, $result['tax']);
        $this->assertEquals(0.00, $result['retention']);
        $this->assertEquals(0.00, $result['total']);
    }

    // ── IvaRetentionCalculator ────────────────────────────────────

    public function test_iva_retention_calculator_applies_iva_and_isr(): void
    {
        $calculator = new IvaRetentionCalculator();
        $result = $calculator->calculate(10000);

        $expectedTax = round(10000 * 0.16, 2);         // 1600.00
        $expectedRetention = round(10000 * 0.10667, 2); // 1066.70
        $expectedTotal = round(10000 + $expectedTax - $expectedRetention, 2); // 10533.30

        $this->assertEquals($expectedTax, $result['tax']);
        $this->assertEquals($expectedRetention, $result['retention']);
        $this->assertEquals($expectedTotal, $result['total']);
    }

    public function test_iva_retention_calculator_with_small_amount(): void
    {
        $calculator = new IvaRetentionCalculator();
        $result = $calculator->calculate(100);

        $expectedTax = round(100 * 0.16, 2);          // 16.00
        $expectedRetention = round(100 * 0.10667, 2);  // 10.67
        $expectedTotal = round(100 + 16.00 - 10.67, 2); // 105.33

        $this->assertEquals($expectedTax, $result['tax']);
        $this->assertEquals($expectedRetention, $result['retention']);
        $this->assertEquals($expectedTotal, $result['total']);
    }

    public function test_iva_retention_calculator_with_zero(): void
    {
        $calculator = new IvaRetentionCalculator();
        $result = $calculator->calculate(0);

        $this->assertEquals(0.00, $result['tax']);
        $this->assertEquals(0.00, $result['retention']);
        $this->assertEquals(0.00, $result['total']);
    }

    // ── ExemptTaxCalculator ───────────────────────────────────────

    public function test_exempt_calculator_returns_zero_taxes(): void
    {
        $calculator = new ExemptTaxCalculator();
        $result = $calculator->calculate(10000);

        $this->assertEquals(0.00, $result['tax']);
        $this->assertEquals(0.00, $result['retention']);
        $this->assertEquals(10000.00, $result['total']);
    }

    public function test_exempt_calculator_total_equals_subtotal(): void
    {
        $calculator = new ExemptTaxCalculator();
        $result = $calculator->calculate(5678.90);

        $this->assertEquals(0.00, $result['tax']);
        $this->assertEquals(0.00, $result['retention']);
        $this->assertEquals(5678.90, $result['total']);
    }

    // ── TaxCalculatorFactory ──────────────────────────────────────

    public function test_factory_returns_iva_calculator(): void
    {
        $calculator = TaxCalculatorFactory::make('iva');
        $this->assertInstanceOf(IvaTaxCalculator::class, $calculator);
    }

    public function test_factory_returns_iva_retention_calculator(): void
    {
        $calculator = TaxCalculatorFactory::make('iva_retention');
        $this->assertInstanceOf(IvaRetentionCalculator::class, $calculator);
    }

    public function test_factory_returns_exempt_calculator(): void
    {
        $calculator = TaxCalculatorFactory::make('exempt');
        $this->assertInstanceOf(ExemptTaxCalculator::class, $calculator);
    }

    public function test_factory_throws_exception_for_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown tax type: invalid');

        TaxCalculatorFactory::make('invalid');
    }

    // ── All calculators return correct keys ───────────────────────

    public function test_all_calculators_return_required_keys(): void
    {
        $types = ['iva', 'iva_retention', 'exempt'];

        foreach ($types as $type) {
            $calculator = TaxCalculatorFactory::make($type);
            $result = $calculator->calculate(1000);

            $this->assertArrayHasKey('tax', $result, "Missing 'tax' key for type: {$type}");
            $this->assertArrayHasKey('retention', $result, "Missing 'retention' key for type: {$type}");
            $this->assertArrayHasKey('total', $result, "Missing 'total' key for type: {$type}");
        }
    }
}
