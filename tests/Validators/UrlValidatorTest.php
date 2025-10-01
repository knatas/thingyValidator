<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Validators;

use ThingyValidator\Tests\ValidatorTestCase;
use ThingyValidator\Tests\DataProviders\CommonDataProvider;
use ThingyValidator\Validators\UrlValidator;
use ThingyValidator\ValidatorInterface;
use ThingyValidator\ValidationContext;

/**
 * Test case for UrlValidator
 *
 * @package ThingyValidator\Tests\Validators
 */
class UrlValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ValidatorInterface
    {
        return new UrlValidator();
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::validUrls
     */
    public function testValidUrlsPass(string $url): void
    {
        $result = $this->assertValid($url);
        $this->assertMessageContains($result, 'Valid');
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::invalidUrls
     */
    public function testInvalidUrlsFail(string $url): void
    {
        $result = $this->assertInvalid($url);
        $this->assertNotNull($result->message);
    }

    public function testEmptyUrlFails(): void
    {
        $this->assertInvalid('');
    }

    public function testNonStringFails(): void
    {
        $this->assertInvalid(12345);
        $this->assertInvalid(null);
        $this->assertInvalid(true);
    }

    public function testValidatorName(): void
    {
        $this->assertEquals('url', $this->validator->getName());
    }

    public function testUrlWithSubdomain(): void
    {
        $this->assertValid('https://sub.example.com');
        $this->assertValid('https://deep.sub.example.com');
    }

    public function testUrlWithPort(): void
    {
        $this->assertValid('https://example.com:8080');
        $this->assertValid('http://localhost:3000');
    }

    public function testUrlWithQueryParameters(): void
    {
        $this->assertValid('https://example.com?key=value&foo=bar');
        $this->assertValid('https://example.com?search=test+query');
    }

    public function testUrlWithFragment(): void
    {
        $this->assertValid('https://example.com#section');
        $this->assertValid('https://example.com/page#heading-1');
    }

    public function testUrlWithComplexPath(): void
    {
        $this->assertValid('https://example.com/path/to/resource');
        $this->assertValid('https://example.com/api/v1/users/123');
    }

    public function testUrlWithIPv4(): void
    {
        $this->assertValid('http://192.168.1.1');
        $this->assertValid('http://127.0.0.1:8080');
    }

    public function testUrlTooLongFails(): void
    {
        $longUrl = 'https://example.com/' . str_repeat('a', 2100);
        $this->assertInvalid($longUrl);
    }

    public function testUrlWithCustomProtocolContext(): void
    {
        $context = new ValidationContext(['allowed_protocols' => ['https']]);

        $this->assertValid('https://example.com', $context);
        $this->assertInvalid('http://example.com', $context);
        $this->assertInvalid('ftp://example.com', $context);
    }

    public function testWhitespaceOnlyFails(): void
    {
        $this->assertInvalid('   ');
        $this->assertInvalid("\t");
    }
}
