<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Core;

use PHPUnit\Framework\TestCase;
use ThingyValidator\ValidatorRegistry;
use ThingyValidator\ValidatorInterface;
use ThingyValidator\ValidationResult;
use ThingyValidator\ValidationContext;

/**
 * Test case for ValidatorRegistry
 *
 * @package ThingyValidator\Tests\Core
 */
class ValidatorRegistryTest extends TestCase
{
    private ValidatorRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        // Reset singleton before each test
        ValidatorRegistry::reset();
        $this->registry = ValidatorRegistry::getInstance();
    }

    protected function tearDown(): void
    {
        ValidatorRegistry::reset();
        parent::tearDown();
    }

    public function testGetInstanceReturnsSingleton(): void
    {
        $instance1 = ValidatorRegistry::getInstance();
        $instance2 = ValidatorRegistry::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testRegisterAddsValidator(): void
    {
        $validator = $this->createMockValidator('test');
        $this->registry->register($validator);

        $this->assertTrue($this->registry->has('test'));
        $this->assertSame($validator, $this->registry->get('test'));
    }

    public function testRegisterThrowsExceptionWhenValidatorExists(): void
    {
        $validator = $this->createMockValidator('test');
        $this->registry->register($validator);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Validator "test" is already registered');

        $this->registry->register($validator);
    }

    public function testRegisterWithOverwriteReplacesValidator(): void
    {
        $validator1 = $this->createMockValidator('test');
        $validator2 = $this->createMockValidator('test');

        $this->registry->register($validator1);
        $this->registry->register($validator2, overwrite: true);

        $this->assertSame($validator2, $this->registry->get('test'));
        $this->assertNotSame($validator1, $this->registry->get('test'));
    }

    public function testGetReturnsNullForUnregisteredValidator(): void
    {
        $this->assertNull($this->registry->get('nonexistent'));
    }

    public function testHasReturnsTrueForRegisteredValidator(): void
    {
        $validator = $this->createMockValidator('test');
        $this->registry->register($validator);

        $this->assertTrue($this->registry->has('test'));
    }

    public function testHasReturnsFalseForUnregisteredValidator(): void
    {
        $this->assertFalse($this->registry->has('nonexistent'));
    }

    public function testUnregisterRemovesValidator(): void
    {
        $validator = $this->createMockValidator('test');
        $this->registry->register($validator);

        $this->registry->unregister('test');

        $this->assertFalse($this->registry->has('test'));
        $this->assertNull($this->registry->get('test'));
    }

    public function testUnregisterNonexistentValidatorDoesNotThrow(): void
    {
        // Should not throw exception
        $this->registry->unregister('nonexistent');
        $this->assertFalse($this->registry->has('nonexistent'));
    }

    public function testGetNamesReturnsAllValidatorNames(): void
    {
        $validator1 = $this->createMockValidator('alpha');
        $validator2 = $this->createMockValidator('beta');
        $validator3 = $this->createMockValidator('gamma');

        $this->registry->register($validator1);
        $this->registry->register($validator2);
        $this->registry->register($validator3);

        $names = $this->registry->getNames();

        $this->assertCount(3, $names);
        $this->assertContains('alpha', $names);
        $this->assertContains('beta', $names);
        $this->assertContains('gamma', $names);
    }

    public function testGetNamesReturnsEmptyArrayWhenNoValidators(): void
    {
        $names = $this->registry->getNames();

        $this->assertIsArray($names);
        $this->assertEmpty($names);
    }

    public function testAllReturnsAllValidators(): void
    {
        $validator1 = $this->createMockValidator('alpha');
        $validator2 = $this->createMockValidator('beta');

        $this->registry->register($validator1);
        $this->registry->register($validator2);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('alpha', $all);
        $this->assertArrayHasKey('beta', $all);
        $this->assertSame($validator1, $all['alpha']);
        $this->assertSame($validator2, $all['beta']);
    }

    public function testClearRemovesAllValidators(): void
    {
        $this->registry->register($this->createMockValidator('alpha'));
        $this->registry->register($this->createMockValidator('beta'));

        $this->registry->clear();

        $this->assertCount(0, $this->registry->getNames());
        $this->assertEmpty($this->registry->all());
    }

    public function testCountReturnsNumberOfValidators(): void
    {
        $this->assertEquals(0, $this->registry->count());

        $this->registry->register($this->createMockValidator('alpha'));
        $this->assertEquals(1, $this->registry->count());

        $this->registry->register($this->createMockValidator('beta'));
        $this->assertEquals(2, $this->registry->count());

        $this->registry->unregister('alpha');
        $this->assertEquals(1, $this->registry->count());

        $this->registry->clear();
        $this->assertEquals(0, $this->registry->count());
    }

    public function testRegisterReturnsFluentInterface(): void
    {
        $validator = $this->createMockValidator('test');
        $result = $this->registry->register($validator);

        $this->assertSame($this->registry, $result);
    }

    public function testUnregisterReturnsFluentInterface(): void
    {
        $result = $this->registry->unregister('test');

        $this->assertSame($this->registry, $result);
    }

    public function testClearReturnsFluentInterface(): void
    {
        $result = $this->registry->clear();

        $this->assertSame($this->registry, $result);
    }

    public function testResetClearsSingletonInstance(): void
    {
        $instance1 = ValidatorRegistry::getInstance();
        $instance1->register($this->createMockValidator('test'));

        ValidatorRegistry::reset();

        $instance2 = ValidatorRegistry::getInstance();

        $this->assertNotSame($instance1, $instance2);
        $this->assertFalse($instance2->has('test'));
    }

    /**
     * Create a mock validator for testing
     */
    private function createMockValidator(string $name): ValidatorInterface
    {
        return new class($name) implements ValidatorInterface {
            public function __construct(private string $name) {}

            public function validate(mixed $value, ?ValidationContext $context = null): ValidationResult
            {
                return ValidationResult::success();
            }

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}
