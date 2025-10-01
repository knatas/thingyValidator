<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Validators;

use ThingyValidator\Tests\ValidatorTestCase;
use ThingyValidator\Validators\LengthValidator;
use ThingyValidator\ValidatorInterface;
use ThingyValidator\ValidationContext;

/**
 * Test case for LengthValidator
 *
 * @package ThingyValidator\Tests\Validators
 */
class LengthValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ValidatorInterface
    {
        return new LengthValidator();
    }

    public function testValidatorName(): void
    {
        $this->assertEquals('length', $this->validator->getName());
    }

    public function testEmptyStringWithMinConstraintFails(): void
    {
        $context = new ValidationContext(['min' => 1, 'max' => 10]);
        $this->assertInvalid('', $context);
    }

    public function testNonStringFails(): void
    {
        $this->assertInvalid(12345);
        $this->assertInvalid(null);
        $this->assertInvalid(true);
        $this->assertInvalid([]);
    }

    public function testStringWithinBoundsPass(): void
    {
        $context = new ValidationContext(['min' => 3, 'max' => 10]);

        $this->assertValid('hello', $context);
        $this->assertValid('test', $context);
        $this->assertValid('abcdefghij', $context);
    }

    public function testStringBelowMinFails(): void
    {
        $context = new ValidationContext(['min' => 5, 'max' => 10]);

        $result = $this->assertInvalid('hi', $context);
        $this->assertNotNull($result->message);
    }

    public function testStringAboveMaxFails(): void
    {
        $context = new ValidationContext(['min' => 3, 'max' => 5]);

        $result = $this->assertInvalid('toolong', $context);
        $this->assertNotNull($result->message);
    }

    public function testExactMinLengthPass(): void
    {
        $context = new ValidationContext(['min' => 5, 'max' => 10]);
        $this->assertValid('hello', $context);
    }

    public function testExactMaxLengthPass(): void
    {
        $context = new ValidationContext(['min' => 3, 'max' => 5]);
        $this->assertValid('hello', $context);
    }

    public function testMultibyteCharacters(): void
    {
        $context = new ValidationContext(['min' => 3, 'max' => 10]);

        // Unicode characters (emoji, accents)
        $this->assertValid('café', $context);      // 4 chars
        $this->assertValid('日本語', $context);     // 3 chars
        $this->assertValid('🎉🎊🎈', $context);    // 3 chars
    }

    public function testZeroMinLength(): void
    {
        $context = new ValidationContext(['min' => 0, 'max' => 10]);

        $this->assertValid('', $context);
        $this->assertValid('a', $context);
    }

    public function testOnlyMinConstraint(): void
    {
        $context = new ValidationContext(['min' => 5]);

        $this->assertInvalid('hi', $context);
        $this->assertValid('hello', $context);
        $this->assertValid('verylongstring', $context);
    }

    public function testOnlyMaxConstraint(): void
    {
        $context = new ValidationContext(['max' => 5]);

        $this->assertValid('', $context);
        $this->assertValid('hi', $context);
        $this->assertValid('hello', $context);
        $this->assertInvalid('toolong', $context);
    }

    public function testWhitespaceCountsTowardLength(): void
    {
        $context = new ValidationContext(['min' => 3, 'max' => 10]);

        $this->assertValid('a b', $context);       // 3 chars
        $this->assertValid('  hello  ', $context); // 9 chars
    }

    public function testSingleCharacterString(): void
    {
        $context = new ValidationContext(['min' => 1, 'max' => 5]);
        $this->assertValid('a', $context);
    }

    public function testVeryLongString(): void
    {
        $context = new ValidationContext(['min' => 0, 'max' => 10000]);

        $longString = str_repeat('a', 5000);
        $this->assertValid($longString, $context);

        $tooLongString = str_repeat('a', 10001);
        $this->assertInvalid($tooLongString, $context);
    }

    public function testMinGreaterThanMaxStillValidates(): void
    {
        // Edge case: if min > max, nothing can pass
        $context = new ValidationContext(['min' => 10, 'max' => 5]);

        $this->assertInvalid('hello', $context);
        $this->assertInvalid('test', $context);
    }
}
