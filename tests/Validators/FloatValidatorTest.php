<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Validators;

use ThingyValidator\Tests\ValidatorTestCase;
use ThingyValidator\Validators\FloatValidator;
use ThingyValidator\ValidatorInterface;
use ThingyValidator\ValidationContext;

/**
 * Test case for FloatValidator
 *
 * @package ThingyValidator\Tests\Validators
 */
class FloatValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ValidatorInterface
    {
        return new FloatValidator();
    }

    public function testValidatorName(): void
    {
        $this->assertEquals('float', $this->validator->getName());
    }

    public function testFloatsPass(): void
    {
        $this->assertValid(3.14);
        $this->assertValid(-2.5);
        $this->assertValid(0.0);
    }

    public function testIntegersPass(): void
    {
        $this->assertValid(42);
        $this->assertValid(-10);
        $this->assertValid(0);
    }

    public function testNumericStringsPass(): void
    {
        $this->assertValid('3.14');
        $this->assertValid('-2.5');
        $this->assertValid('100');
    }

    public function testNonNumericFails(): void
    {
        $this->assertInvalid('abc');
        $this->assertInvalid('12.3abc');
        $this->assertInvalid(null);
        $this->assertInvalid(true);
    }

    public function testStrictModeRejectsStrings(): void
    {
        $context = new ValidationContext(['strict' => true]);

        $this->assertValid(3.14, $context);
        $this->assertValid(42, $context);
        $this->assertInvalid('3.14', $context);
    }

    public function testPrecisionConstraint(): void
    {
        $context = new ValidationContext(['precision' => 2]);

        $this->assertValid(3.14, $context);
        $this->assertValid(3.1, $context);
        $this->assertValid(3.0, $context);
        $this->assertInvalid(3.145, $context);
    }

    public function testRangeConstraints(): void
    {
        $context = new ValidationContext(['min' => 0.0, 'max' => 100.0]);

        $this->assertValid(50.5, $context);
        $this->assertValid(0.0, $context);
        $this->assertValid(100.0, $context);
        $this->assertInvalid(-0.1, $context);
        $this->assertInvalid(100.1, $context);
    }

    public function testPrecisionZeroAllowsIntegers(): void
    {
        $context = new ValidationContext(['precision' => 0]);

        $this->assertValid(42.0, $context);
        $this->assertValid(100.0, $context);
        $this->assertInvalid(3.1, $context);
    }

    public function testCombinedConstraints(): void
    {
        $context = new ValidationContext([
            'min' => 0.0,
            'max' => 10.0,
            'precision' => 2
        ]);

        $this->assertValid(5.25, $context);
        $this->assertValid(9.99, $context);
        $this->assertInvalid(10.1, $context);     // Out of range
        $this->assertInvalid(5.125, $context);    // Too much precision
    }
}
