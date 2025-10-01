<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Validators;

use ThingyValidator\Tests\ValidatorTestCase;
use ThingyValidator\Tests\DataProviders\CommonDataProvider;
use ThingyValidator\Validators\NumberValidator;
use ThingyValidator\ValidatorInterface;

/**
 * Test case for NumberValidator
 *
 * @package ThingyValidator\Tests\Validators
 */
class NumberValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ValidatorInterface
    {
        return new NumberValidator();
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::validNumbers
     */
    public function testValidNumbersPass(mixed $number): void
    {
        $this->assertValid($number);
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::invalidNumbers
     */
    public function testInvalidNumbersFail(mixed $value): void
    {
        $this->assertInvalid($value);
    }

    public function testValidatorName(): void
    {
        $this->assertEquals('number', $this->validator->getName());
    }

    public function testZero(): void
    {
        $this->assertValid(0);
        $this->assertValid(0.0);
        $this->assertValid('0');
    }

    public function testNegativeNumbers(): void
    {
        $this->assertValid(-42);
        $this->assertValid(-3.14);
        $this->assertValid('-100');
    }

    public function testScientificNotation(): void
    {
        $this->assertValid('1e10');
        $this->assertValid('1.5e-5');
        $this->assertValid('2.5E+3');
    }

    public function testHexadecimalFails(): void
    {
        $this->assertInvalid('0xFF');
        $this->assertInvalid('0x1A');
    }
}
