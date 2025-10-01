<?php

declare(strict_types=1);

namespace ThingyValidator\Tests;

use PHPUnit\Framework\TestCase;
use ThingyValidator\ValidatorInterface;
use ThingyValidator\ValidationResult;
use ThingyValidator\ValidationContext;

/**
 * Base test case for validator testing
 *
 * Provides common assertions and helpers for testing validators.
 *
 * @package ThingyValidator\Tests
 */
abstract class ValidatorTestCase extends TestCase
{
    /**
     * @var ValidatorInterface The validator instance being tested
     */
    protected ValidatorInterface $validator;

    /**
     * Get the validator instance to test
     *
     * @return ValidatorInterface
     */
    abstract protected function createValidator(): ValidatorInterface;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->createValidator();
    }

    /**
     * Assert that a value passes validation
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional context
     * @param string $message Optional assertion message
     * @return ValidationResult The validation result
     */
    protected function assertValid(mixed $value, ?ValidationContext $context = null, string $message = ''): ValidationResult
    {
        $result = $this->validator->validate($value, $context);

        $this->assertTrue(
            $result->isValid,
            $message ?: sprintf(
                'Expected value to be valid but got: %s',
                $result->message ?? 'no message'
            )
        );

        return $result;
    }

    /**
     * Assert that a value fails validation
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional context
     * @param string $message Optional assertion message
     * @return ValidationResult The validation result
     */
    protected function assertInvalid(mixed $value, ?ValidationContext $context = null, string $message = ''): ValidationResult
    {
        $result = $this->validator->validate($value, $context);

        $this->assertFalse(
            $result->isValid,
            $message ?: sprintf(
                'Expected value to be invalid but it passed validation'
            )
        );

        return $result;
    }

    /**
     * Assert that validation result contains specific error
     *
     * @param ValidationResult $result The validation result
     * @param string $key Error key to check
     * @param mixed $expectedValue Expected error value (optional)
     */
    protected function assertHasError(ValidationResult $result, string $key, mixed $expectedValue = null): void
    {
        $this->assertArrayHasKey(
            $key,
            $result->errors,
            sprintf('Expected error key "%s" not found in errors', $key)
        );

        if ($expectedValue !== null) {
            $this->assertEquals(
                $expectedValue,
                $result->errors[$key],
                sprintf('Error value for key "%s" does not match expected', $key)
            );
        }
    }

    /**
     * Assert that validation result message contains string
     *
     * @param ValidationResult $result The validation result
     * @param string $needle String to search for
     */
    protected function assertMessageContains(ValidationResult $result, string $needle): void
    {
        $this->assertNotNull($result->message, 'Result message is null');
        $this->assertStringContainsString(
            $needle,
            $result->message,
            sprintf('Expected message to contain "%s"', $needle)
        );
    }

    /**
     * Test basic validator properties
     */
    public function testValidatorHasName(): void
    {
        $name = $this->validator->getName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * Test validator returns ValidationResult
     */
    public function testValidatorReturnsValidationResult(): void
    {
        $result = $this->validator->validate('test');
        $this->assertInstanceOf(ValidationResult::class, $result);
    }

    /**
     * Create validation context with data
     *
     * @param array<string, mixed> $data Context data
     * @return ValidationContext
     */
    protected function createContext(array $data = []): ValidationContext
    {
        return new ValidationContext($data);
    }

    /**
     * Data provider for invalid type testing
     *
     * @return array<string, array<mixed>>
     */
    public static function provideInvalidTypes(): array
    {
        return [
            'null' => [null],
            'boolean true' => [true],
            'boolean false' => [false],
            'array' => [[]],
            'object' => [new \stdClass()],
        ];
    }

    /**
     * Data provider for empty values
     *
     * @return array<string, array<mixed>>
     */
    public static function provideEmptyValues(): array
    {
        return [
            'empty string' => [''],
            'whitespace only' => ['   '],
            'tab only' => ["\t"],
            'newline only' => ["\n"],
        ];
    }
}
