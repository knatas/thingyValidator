<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Validators;

use ThingyValidator\Tests\ValidatorTestCase;
use ThingyValidator\Tests\DataProviders\CommonDataProvider;
use ThingyValidator\Validators\PhoneValidator;
use ThingyValidator\ValidatorInterface;

/**
 * Test case for PhoneValidator
 *
 * @package ThingyValidator\Tests\Validators
 */
class PhoneValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ValidatorInterface
    {
        return new PhoneValidator();
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::validPhones
     */
    public function testValidPhonesPass(string $phone): void
    {
        $result = $this->assertValid($phone);
        $this->assertMessageContains($result, 'valid');
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::invalidPhones
     */
    public function testInvalidPhonesFail(string $phone): void
    {
        $result = $this->assertInvalid($phone);
        $this->assertNotNull($result->message);
    }

    public function testEmptyPhoneFails(): void
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
        $this->assertEquals('phone', $this->validator->getName());
    }

    public function testInternationalE164Format(): void
    {
        $this->assertValid('+37061234567');
        $this->assertValid('+441234567890');
        $this->assertValid('+12345678901');
    }

    public function testUSFormats(): void
    {
        $this->assertValid('+1 (555) 123-4567');
        $this->assertValid('+1-555-123-4567');
        $this->assertValid('(555) 123-4567');
        $this->assertValid('555-123-4567');
    }

    public function testEuropeanFormats(): void
    {
        $this->assertValid('+44 20 7123 4567');
        $this->assertValid('+33 1 23 45 67 89');
        $this->assertValid('+49 30 12345678');
    }

    public function testMinimumLengthEnforced(): void
    {
        $this->assertInvalid('123456');  // Too short (6 digits)
        $this->assertValid('1234567');   // Minimum (7 digits)
    }

    public function testMaximumLengthEnforced(): void
    {
        $this->assertValid('123456789012345');  // Maximum (15 digits)
        $this->assertInvalid('1234567890123456'); // Too long (16 digits)
    }

    public function testPhoneWithOnlySpacesFails(): void
    {
        $this->assertInvalid('   ');
        $this->assertInvalid('- - -');
    }

    public function testPhoneWithMultiplePlusSignsFails(): void
    {
        $this->assertInvalid('++37061234567');
        $this->assertInvalid('+370+61234567');
    }

    public function testPhoneWithPlusInMiddleFails(): void
    {
        $this->assertInvalid('370+61234567');
    }

    public function testLocalFormatsWithoutCountryCode(): void
    {
        $this->assertValid('(123) 456-7890');
        $this->assertValid('123-456-7890');
        $this->assertValid('1234567890');
    }

    public function testWhitespaceOnlyFails(): void
    {
        $this->assertInvalid('   ');
        $this->assertInvalid("\t");
    }
}
