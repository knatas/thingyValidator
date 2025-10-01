<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Validators;

use ThingyValidator\Tests\ValidatorTestCase;
use ThingyValidator\Validators\IntegerValidator;
use ThingyValidator\ValidatorInterface;
use ThingyValidator\ValidationContext;

/**
 * Test case for IntegerValidator
 *
 * @package ThingyValidator\Tests\Validators
 */
class IntegerValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ValidatorInterface
    {
        return new IntegerValidator();
    }

    public function testValidatorName(): void
    {
        $this->assertEquals('integer', $this->validator->getName());
    }

    public function testIntegersPassWithStrictMode(): void
    {
        $context = new ValidationContext(['strict' => true]);
        $this->assertValid(42, $context);
        $this->assertValid(-10, $context);
        $this->assertValid(0, $context);
    }

    public function testNumericStringsPass(): void
    {
        $this->assertValid('123');
        $this->assertValid('-456');
        $this->assertValid('0');
    }

    public function testFloatsFail(): void
    {
        $this->assertInvalid(3.14);
        $this->assertInvalid(-2.5);
    }

    public function testFloatStringsWithDecimalFail(): void
    {
        $this->assertInvalid('3.14');
        $this->assertInvalid('100.5');
    }

    public function testNonNumericFails(): void
    {
        $this->assertInvalid('abc');
        $this->assertInvalid('12abc');
        $this->assertInvalid(null);
        $this->assertInvalid(true);
    }

    public function testStrictModeRejectsStrings(): void
    {
        $context = new ValidationContext(['strict' => true]);

        $this->assertValid(42, $context);
        $this->assertInvalid('42', $context);
    }

    public function testRangeConstraints(): void
    {
        $context = new ValidationContext(['min' => 10, 'max' => 100, 'strict' => true]);

        $this->assertValid(50, $context);
        $this->assertValid(10, $context);  // Min boundary
        $this->assertValid(100, $context); // Max boundary
        $this->assertInvalid(9, $context);
        $this->assertInvalid(101, $context);
    }

    public function testMinConstraintOnly(): void
    {
        $context = new ValidationContext(['min' => 0, 'strict' => true]);

        $this->assertValid(0, $context);
        $this->assertValid(100, $context);
        $this->assertInvalid(-1, $context);
    }

    public function testMaxConstraintOnly(): void
    {
        $context = new ValidationContext(['max' => 100, 'strict' => true]);

        $this->assertValid(0, $context);
        $this->assertValid(100, $context);
        $this->assertInvalid(101, $context);
    }

    public function testNegativeRange(): void
    {
        $context = new ValidationContext(['min' => -100, 'max' => -10, 'strict' => true]);

        $this->assertValid(-50, $context);
        $this->assertInvalid(0, $context);
        $this->assertInvalid(-101, $context);
    }
}
