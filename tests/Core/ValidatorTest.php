<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Core;

use PHPUnit\Framework\TestCase;
use ThingyValidator\Validator;
use ThingyValidator\ValidatorRegistry;
use ThingyValidator\ValidationContext;

/**
 * Test case for Validator facade class
 *
 * @package ThingyValidator\Tests\Core
 */
class ValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ValidatorRegistry::reset();
    }

    protected function tearDown(): void
    {
        ValidatorRegistry::reset();
        parent::tearDown();
    }

    public function testConstructorAutoRegistersBuiltInValidators(): void
    {
        $validator = new Validator();
        $registry = $validator->getRegistry();

        // Check that built-in validators are registered
        $this->assertTrue($registry->has('email'));
        $this->assertTrue($registry->has('url'));
        $this->assertTrue($registry->has('alpha'));
        $this->assertTrue($registry->has('alphanumeric'));
        $this->assertTrue($registry->has('length'));
    }

    public function testConstructorWithAutoRegisterFalseDoesNotRegister(): void
    {
        $validator = new Validator(null, false);
        $registry = $validator->getRegistry();

        $this->assertFalse($registry->has('email'));
        $this->assertFalse($registry->has('url'));
    }

    public function testConstructorAcceptsCustomRegistry(): void
    {
        $customRegistry = ValidatorRegistry::getInstance();
        $validator = new Validator($customRegistry, false);

        $this->assertSame($customRegistry, $validator->getRegistry());
    }

    public function testWithContextSetsContext(): void
    {
        $validator = new Validator();
        $context = new ValidationContext(['key' => 'value']);

        $result = $validator->withContext($context);

        $this->assertSame($validator, $result); // Fluent interface
        $this->assertSame($context, $validator->getContext());
    }

    public function testGetContextReturnsNullInitially(): void
    {
        $validator = new Validator();
        $this->assertNull($validator->getContext());
    }

    public function testGetRegistryReturnsRegistry(): void
    {
        $validator = new Validator();
        $registry = $validator->getRegistry();

        $this->assertInstanceOf(ValidatorRegistry::class, $registry);
    }

    public function testIsEmailWithValidEmail(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isEmail('test@example.com'));
    }

    public function testIsEmailWithInvalidEmail(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isEmail('invalid'));
    }

    public function testIsUrlWithValidUrl(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isUrl('https://example.com'));
    }

    public function testIsUrlWithInvalidUrl(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isUrl('not-a-url'));
    }

    public function testIsPhoneWithValidPhone(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isPhone('+37061234567'));
    }

    public function testIsPhoneWithInvalidPhone(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isPhone('abc'));
    }

    public function testIsAlphaWithValidAlpha(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isAlpha('hello'));
    }

    public function testIsAlphaWithInvalidAlpha(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isAlpha('hello123'));
    }

    public function testIsAlphanumericWithValidAlphanumeric(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isAlphanumeric('hello123'));
    }

    public function testIsAlphanumericWithInvalidAlphanumeric(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isAlphanumeric('hello-123'));
    }

    public function testIsNumberWithValidNumber(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isNumber(42));
        $this->assertTrue($validator->isNumber(3.14));
        $this->assertTrue($validator->isNumber('123'));
    }

    public function testIsNumberWithInvalidNumber(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isNumber('abc'));
    }

    public function testIsLengthWithValidLength(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isLength('hello', 3, 10));
    }

    public function testIsLengthWithInvalidLength(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isLength('hi', 3, 10));
        $this->assertFalse($validator->isLength('verylongstring', 3, 10));
    }

    public function testIsIbanWithValidIban(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isIban('LT601010012345678901'));
    }

    public function testIsIbanWithInvalidIban(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isIban('INVALID'));
    }

    public function testIsUuidWithValidUuid(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isUuid('550e8400-e29b-41d4-a716-446655440000'));
    }

    public function testIsUuidWithInvalidUuid(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isUuid('not-a-uuid'));
    }

    public function testConvenienceMethodsFallBackWhenValidatorNotRegistered(): void
    {
        $validator = new Validator(null, false); // No auto-register

        // Should use fallback validation
        $this->assertTrue($validator->isEmail('test@example.com'));
        $this->assertTrue($validator->isNumber(42));
        $this->assertTrue($validator->isAlpha('hello'));
    }

    public function testRegisterValidatorAddsToRegistry(): void
    {
        $validator = new Validator(null, false);

        $customValidator = $this->createMockValidator('custom');
        $validator->registerValidator($customValidator);

        $this->assertTrue($validator->getRegistry()->has('custom'));
    }

    public function testValidateUsesRegisteredValidator(): void
    {
        $validator = new Validator();
        $result = $validator->validate('email', 'test@example.com');

        $this->assertTrue($result->isValid);
    }

    public function testValidateThrowsExceptionForUnregisteredValidator(): void
    {
        $validator = new Validator(null, false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Validator "nonexistent" is not registered');

        $validator->validate('nonexistent', 'value');
    }

    public function testValidateUsesContextFromMethod(): void
    {
        $validator = new Validator();
        $context = new ValidationContext(['key' => 'value']);

        // Should not throw, context is passed
        $result = $validator->validate('email', 'test@example.com', $context);

        $this->assertInstanceOf(\ThingyValidator\ValidationResult::class, $result);
    }

    public function testValidateUsesInstanceContext(): void
    {
        $validator = new Validator();
        $context = new ValidationContext(['key' => 'value']);
        $validator->withContext($context);

        // Should use instance context
        $result = $validator->validate('email', 'test@example.com');

        $this->assertInstanceOf(\ThingyValidator\ValidationResult::class, $result);
    }

    /**
     * Create a mock validator for testing
     */
    private function createMockValidator(string $name): \ThingyValidator\ValidatorInterface
    {
        return new class($name) implements \ThingyValidator\ValidatorInterface {
            public function __construct(private string $name) {}

            public function validate(mixed $value, ?\ThingyValidator\ValidationContext $context = null): \ThingyValidator\ValidationResult
            {
                return \ThingyValidator\ValidationResult::success();
            }

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}
