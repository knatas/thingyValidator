<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Core;

use PHPUnit\Framework\TestCase;
use ThingyValidator\ValidationContext;

/**
 * Test case for ValidationContext
 *
 * @package ThingyValidator\Tests\Core
 */
class ValidationContextTest extends TestCase
{
    public function testConstructorWithEmptyData(): void
    {
        $context = new ValidationContext();
        $this->assertEmpty($context->all());
    }

    public function testConstructorWithInitialData(): void
    {
        $data = ['key' => 'value', 'number' => 42];
        $context = new ValidationContext($data);

        $this->assertEquals($data, $context->all());
    }

    public function testSetAddsValue(): void
    {
        $context = new ValidationContext();
        $result = $context->set('key', 'value');

        $this->assertSame($context, $result); // Fluent interface
        $this->assertEquals('value', $context->get('key'));
    }

    public function testSetOverwritesExistingValue(): void
    {
        $context = new ValidationContext(['key' => 'old']);
        $context->set('key', 'new');

        $this->assertEquals('new', $context->get('key'));
    }

    public function testGetReturnsValue(): void
    {
        $context = new ValidationContext(['key' => 'value']);
        $this->assertEquals('value', $context->get('key'));
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        $context = new ValidationContext();
        $this->assertEquals('default', $context->get('missing', 'default'));
    }

    public function testGetReturnsNullForMissingKeyWithoutDefault(): void
    {
        $context = new ValidationContext();
        $this->assertNull($context->get('missing'));
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $context = new ValidationContext(['key' => 'value']);
        $this->assertTrue($context->has('key'));
    }

    public function testHasReturnsFalseForMissingKey(): void
    {
        $context = new ValidationContext();
        $this->assertFalse($context->has('missing'));
    }

    public function testHasReturnsTrueEvenForNullValue(): void
    {
        $context = new ValidationContext(['key' => null]);
        $this->assertTrue($context->has('key'));
    }

    public function testRemoveDeletesKey(): void
    {
        $context = new ValidationContext(['key' => 'value']);
        $result = $context->remove('key');

        $this->assertSame($context, $result); // Fluent interface
        $this->assertFalse($context->has('key'));
    }

    public function testRemoveNonexistentKeyDoesNotThrow(): void
    {
        $context = new ValidationContext();
        $context->remove('nonexistent');

        $this->assertFalse($context->has('nonexistent'));
    }

    public function testAllReturnsAllData(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'];
        $context = new ValidationContext($data);

        $this->assertEquals($data, $context->all());
    }

    public function testClearRemovesAllData(): void
    {
        $context = new ValidationContext(['key1' => 'value1', 'key2' => 'value2']);
        $result = $context->clear();

        $this->assertSame($context, $result); // Fluent interface
        $this->assertEmpty($context->all());
        $this->assertFalse($context->has('key1'));
        $this->assertFalse($context->has('key2'));
    }

    public function testContextCanStoreVariousTypes(): void
    {
        $context = new ValidationContext();

        $context->set('string', 'text');
        $context->set('integer', 42);
        $context->set('float', 3.14);
        $context->set('boolean', true);
        $context->set('array', ['nested' => 'value']);
        $context->set('object', new \stdClass());
        $context->set('null', null);

        $this->assertIsString($context->get('string'));
        $this->assertIsInt($context->get('integer'));
        $this->assertIsFloat($context->get('float'));
        $this->assertIsBool($context->get('boolean'));
        $this->assertIsArray($context->get('array'));
        $this->assertIsObject($context->get('object'));
        $this->assertNull($context->get('null'));
    }

    public function testFluentInterface(): void
    {
        $context = new ValidationContext();

        $result = $context
            ->set('key1', 'value1')
            ->set('key2', 'value2')
            ->remove('key1')
            ->set('key3', 'value3');

        $this->assertSame($context, $result);
        $this->assertFalse($context->has('key1'));
        $this->assertTrue($context->has('key2'));
        $this->assertTrue($context->has('key3'));
    }

    public function testContextIsolation(): void
    {
        $context1 = new ValidationContext(['key' => 'value1']);
        $context2 = new ValidationContext(['key' => 'value2']);

        $this->assertEquals('value1', $context1->get('key'));
        $this->assertEquals('value2', $context2->get('key'));

        $context1->set('key', 'modified');

        $this->assertEquals('modified', $context1->get('key'));
        $this->assertEquals('value2', $context2->get('key'));
    }

    public function testComplexNestedData(): void
    {
        $complexData = [
            'config' => [
                'min' => 5,
                'max' => 100,
                'options' => ['strict' => true, 'case_sensitive' => false],
            ],
            'metadata' => [
                'source' => 'api',
                'timestamp' => time(),
            ],
        ];

        $context = new ValidationContext($complexData);

        $this->assertEquals($complexData, $context->all());
        $this->assertEquals($complexData['config'], $context->get('config'));
        $this->assertEquals($complexData['metadata']['source'], $context->get('metadata')['source']);
    }
}
