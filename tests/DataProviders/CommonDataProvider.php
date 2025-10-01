<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\DataProviders;

/**
 * Common test data providers for validation testing
 *
 * Provides reusable test data sets for various validation scenarios.
 *
 * @package ThingyValidator\Tests\DataProviders
 */
class CommonDataProvider
{
    /**
     * Valid email addresses
     *
     * @return array<string, array<string>>
     */
    public static function validEmails(): array
    {
        return [
            'simple' => ['test@example.com'],
            'with subdomain' => ['user@mail.example.com'],
            'with plus' => ['user+tag@example.com'],
            'with dots' => ['first.last@example.com'],
            'with numbers' => ['user123@example123.com'],
            'short domain' => ['a@b.co'],
        ];
    }

    /**
     * Invalid email addresses
     *
     * @return array<string, array<string>>
     */
    public static function invalidEmails(): array
    {
        return [
            'missing @' => ['userexample.com'],
            'missing domain' => ['user@'],
            'missing user' => ['@example.com'],
            'double @' => ['user@@example.com'],
            'spaces' => ['user @example.com'],
        ];
    }

    /**
     * Valid URLs
     *
     * @return array<string, array<string>>
     */
    public static function validUrls(): array
    {
        return [
            'http' => ['http://example.com'],
            'https' => ['https://example.com'],
            'with path' => ['https://example.com/path/to/resource'],
            'with query' => ['https://example.com?key=value'],
            'with port' => ['https://example.com:8080'],
            'with fragment' => ['https://example.com#section'],
            'ftp' => ['ftp://ftp.example.com'],
        ];
    }

    /**
     * Invalid URLs
     *
     * @return array<string, array<string>>
     */
    public static function invalidUrls(): array
    {
        return [
            'no scheme' => ['example.com'],
            'invalid scheme' => ['javascript:alert(1)'],
            'spaces' => ['http://example .com'],
            'no host' => ['http://'],
            'incomplete' => ['http:/example.com'],
        ];
    }

    /**
     * Valid phone numbers
     *
     * @return array<string, array<string>>
     */
    public static function validPhones(): array
    {
        return [
            'E.164' => ['+37061234567'],
            'US format' => ['+1 (555) 123-4567'],
            'with spaces' => ['+44 20 7123 4567'],
            'with dashes' => ['+1-555-123-4567'],
            'local format' => ['(555) 123-4567'],
            'simple' => ['1234567890'],
        ];
    }

    /**
     * Invalid phone numbers
     *
     * @return array<string, array<string>>
     */
    public static function invalidPhones(): array
    {
        return [
            'too short' => ['123'],
            'too long' => ['12345678901234567890'],
            'letters' => ['abc-def-ghij'],
            'no digits' => ['()- -'],
            'multiple plus' => ['+1+2345678901'],
        ];
    }

    /**
     * Valid numeric values
     *
     * @return array<string, array<mixed>>
     */
    public static function validNumbers(): array
    {
        return [
            'integer' => [42],
            'float' => [3.14],
            'negative integer' => [-10],
            'negative float' => [-2.5],
            'zero' => [0],
            'numeric string' => ['123'],
            'float string' => ['45.67'],
        ];
    }

    /**
     * Invalid numeric values
     *
     * @return array<string, array<mixed>>
     */
    public static function invalidNumbers(): array
    {
        return [
            'string' => ['abc'],
            'mixed' => ['12abc'],
            'boolean' => [true],
            'null' => [null],
            'array' => [[]],
            'object' => [new \stdClass()],
        ];
    }

    /**
     * Valid integer values
     *
     * @return array<string, array<mixed>>
     */
    public static function validIntegers(): array
    {
        return [
            'positive' => [42],
            'negative' => [-10],
            'zero' => [0],
            'large' => [999999],
            'numeric string' => ['123'],
        ];
    }

    /**
     * Invalid integer values
     *
     * @return array<string, array<mixed>>
     */
    public static function invalidIntegers(): array
    {
        return [
            'float' => [3.14],
            'float string' => ['3.14'],
            'string' => ['abc'],
            'boolean' => [false],
            'null' => [null],
        ];
    }

    /**
     * Valid alphabetic strings
     *
     * @return array<string, array<string>>
     */
    public static function validAlpha(): array
    {
        return [
            'lowercase' => ['hello'],
            'uppercase' => ['WORLD'],
            'mixed case' => ['HelloWorld'],
            'single char' => ['a'],
        ];
    }

    /**
     * Invalid alphabetic strings
     *
     * @return array<string, array<string>>
     */
    public static function invalidAlpha(): array
    {
        return [
            'with numbers' => ['hello123'],
            'with spaces' => ['hello world'],
            'with special chars' => ['hello!'],
            'empty' => [''],
        ];
    }

    /**
     * Valid alphanumeric strings
     *
     * @return array<string, array<string>>
     */
    public static function validAlphanumeric(): array
    {
        return [
            'letters only' => ['hello'],
            'numbers only' => ['12345'],
            'mixed' => ['hello123'],
            'uppercase' => ['HELLO123'],
            'single char' => ['a'],
        ];
    }

    /**
     * Invalid alphanumeric strings
     *
     * @return array<string, array<string>>
     */
    public static function invalidAlphanumeric(): array
    {
        return [
            'with spaces' => ['hello 123'],
            'with special chars' => ['hello-123'],
            'with underscore' => ['hello_123'],
            'empty' => [''],
        ];
    }

    /**
     * Valid IBAN examples
     *
     * @return array<string, array<string>>
     */
    public static function validIbans(): array
    {
        return [
            'Lithuania' => ['LT601010012345678901'],
            'Germany' => ['DE89370400440532013000'],
            'UK' => ['GB82WEST12345698765432'],
            'France' => ['FR1420041010050500013M02606'],
            'Netherlands' => ['NL91ABNA0417164300'],
        ];
    }

    /**
     * Invalid IBAN examples
     *
     * @return array<string, array<string>>
     */
    public static function invalidIbans(): array
    {
        return [
            'too short' => ['LT60101001234'],
            'invalid checksum' => ['LT601010012345678900'],
            'invalid country' => ['XX601010012345678901'],
            'no country code' => ['601010012345678901'],
            'with spaces wrong length' => ['LT 6010 1001 2345'],
        ];
    }

    /**
     * Valid UUID v4 examples
     *
     * @return array<string, array<string>>
     */
    public static function validUuids(): array
    {
        return [
            'v4 example 1' => ['550e8400-e29b-41d4-a716-446655440000'],
            'v4 example 2' => ['6ba7b810-9dad-41d1-80b4-00c04fd430c8'],
            'v4 example 3' => ['f47ac10b-58cc-4372-a567-0e02b2c3d479'],
        ];
    }

    /**
     * Invalid UUID examples
     *
     * @return array<string, array<string>>
     */
    public static function invalidUuids(): array
    {
        return [
            'missing dashes' => ['550e8400e29b41d4a716446655440000'],
            'wrong format' => ['550e8400-e29b-41d4-a716'],
            'invalid chars' => ['550e8400-e29b-41d4-a716-44665544000g'],
            'too short' => ['550e8400-e29b-41d4'],
            'not uuid' => ['not-a-uuid-at-all-here'],
        ];
    }

    /**
     * Edge case string lengths
     *
     * @return array<string, array{string, int}>
     */
    public static function stringLengths(): array
    {
        return [
            'empty' => ['', 0],
            'single char' => ['a', 1],
            'short' => ['hello', 5],
            'medium' => [str_repeat('x', 50), 50],
            'long' => [str_repeat('y', 1000), 1000],
        ];
    }

    /**
     * Invalid type values (for type checking)
     *
     * @return array<string, array<mixed>>
     */
    public static function invalidTypes(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'array' => [[]],
            'object' => [new \stdClass()],
            'resource' => [fopen('php://memory', 'r')],
        ];
    }

    /**
     * Empty and whitespace strings
     *
     * @return array<string, array<string>>
     */
    public static function emptyStrings(): array
    {
        return [
            'empty' => [''],
            'space' => [' '],
            'spaces' => ['   '],
            'tab' => ["\t"],
            'newline' => ["\n"],
            'mixed whitespace' => [" \t\n "],
        ];
    }
}
