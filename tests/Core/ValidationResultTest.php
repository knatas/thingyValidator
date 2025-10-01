<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Core;

use PHPUnit\Framework\TestCase;
use ThingyValidator\ValidationResult;
use ThingyValidator\ValidationResultType;

/**
 * Test case for ValidationResult
 *
 * @package ThingyValidator\Tests\Core
 */
class ValidationResultTest extends TestCase
{
    public function testConstructorCreatesValidResult(): void
    {
        $result = new ValidationResult(
            isValid: true,
            message: 'Success',
            errors: [],
            type: ValidationResultType::Success
        );

        $this->assertTrue($result->isValid);
        $this->assertEquals('Success', $result->message);
        $this->assertEmpty($result->errors);
        $this->assertEquals(ValidationResultType::Success, $result->type);
    }

    public function testConstructorCreatesInvalidResult(): void
    {
        $errors = ['field' => 'error message'];
        $result = new ValidationResult(
            isValid: false,
            message: 'Failed',
            errors: $errors,
            type: ValidationResultType::Failure
        );

        $this->assertFalse($result->isValid);
        $this->assertEquals('Failed', $result->message);
        $this->assertEquals($errors, $result->errors);
        $this->assertEquals(ValidationResultType::Failure, $result->type);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $result = new ValidationResult(isValid: true);

        $this->assertTrue($result->isValid);
        $this->assertNull($result->message);
        $this->assertEmpty($result->errors);
        $this->assertNull($result->type);
    }

    public function testSuccessFactoryMethod(): void
    {
        $result = ValidationResult::success('Everything OK');

        $this->assertTrue($result->isValid);
        $this->assertEquals('Everything OK', $result->message);
        $this->assertEmpty($result->errors);
        $this->assertEquals(ValidationResultType::Success, $result->type);
    }

    public function testSuccessFactoryMethodWithoutMessage(): void
    {
        $result = ValidationResult::success();

        $this->assertTrue($result->isValid);
        $this->assertNull($result->message);
        $this->assertEmpty($result->errors);
        $this->assertEquals(ValidationResultType::Success, $result->type);
    }

    public function testFailureFactoryMethod(): void
    {
        $errors = ['key' => 'value'];
        $result = ValidationResult::failure('Something wrong', $errors);

        $this->assertFalse($result->isValid);
        $this->assertEquals('Something wrong', $result->message);
        $this->assertEquals($errors, $result->errors);
        $this->assertEquals(ValidationResultType::Failure, $result->type);
    }

    public function testFailureFactoryMethodWithoutMessageAndErrors(): void
    {
        $result = ValidationResult::failure();

        $this->assertFalse($result->isValid);
        $this->assertNull($result->message);
        $this->assertEmpty($result->errors);
        $this->assertEquals(ValidationResultType::Failure, $result->type);
    }

    public function testWarningFactoryMethod(): void
    {
        $errors = ['warning' => 'deprecated'];
        $result = ValidationResult::warning('Deprecated usage', $errors);

        $this->assertTrue($result->isValid);
        $this->assertEquals('Deprecated usage', $result->message);
        $this->assertEquals($errors, $result->errors);
        $this->assertEquals(ValidationResultType::Warning, $result->type);
    }

    public function testWarningFactoryMethodWithoutMessageAndErrors(): void
    {
        $result = ValidationResult::warning();

        $this->assertTrue($result->isValid);
        $this->assertNull($result->message);
        $this->assertEmpty($result->errors);
        $this->assertEquals(ValidationResultType::Warning, $result->type);
    }

    public function testResultIsImmutable(): void
    {
        $result = new ValidationResult(isValid: true, message: 'Test');

        // Readonly properties should exist
        $this->assertTrue(property_exists($result, 'isValid'));
        $this->assertTrue(property_exists($result, 'message'));
        $this->assertTrue(property_exists($result, 'errors'));
        $this->assertTrue(property_exists($result, 'type'));
    }

    public function testErrorsArrayCanContainComplexData(): void
    {
        $errors = [
            'field' => 'error',
            'nested' => ['key' => 'value'],
            'number' => 42,
            'boolean' => true,
        ];

        $result = ValidationResult::failure('Error', $errors);

        $this->assertEquals($errors, $result->errors);
        $this->assertEquals('error', $result->errors['field']);
        $this->assertEquals(['key' => 'value'], $result->errors['nested']);
        $this->assertEquals(42, $result->errors['number']);
        $this->assertTrue($result->errors['boolean']);
    }
}
