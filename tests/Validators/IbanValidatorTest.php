<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Validators;

use ThingyValidator\Tests\ValidatorTestCase;
use ThingyValidator\Tests\DataProviders\CommonDataProvider;
use ThingyValidator\Validators\IbanValidator;
use ThingyValidator\ValidatorInterface;

/**
 * Test case for IbanValidator
 *
 * @package ThingyValidator\Tests\Validators
 */
class IbanValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ValidatorInterface
    {
        return new IbanValidator();
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::validIbans
     */
    public function testValidIbansPass(string $iban): void
    {
        $result = $this->assertValid($iban);
        $this->assertMessageContains($result, 'valid');
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::invalidIbans
     */
    public function testInvalidIbansFail(string $iban): void
    {
        $result = $this->assertInvalid($iban);
        $this->assertNotNull($result->message);
    }

    public function testEmptyIbanFails(): void
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
        $this->assertEquals('iban', $this->validator->getName());
    }

    public function testIbanWithSpacesIsNormalized(): void
    {
        $this->assertValid('LT60 1010 0123 4567 8901');
        $this->assertValid('DE89 3704 0044 0532 0130 00');
    }

    public function testCountrySpecificLengths(): void
    {
        // Lithuania (20 chars)
        $this->assertValid('LT601010012345678901');

        // Germany (22 chars)
        $this->assertValid('DE89370400440532013000');

        // UK (22 chars)
        $this->assertValid('GB82WEST12345698765432');

        // Netherlands (18 chars)
        $this->assertValid('NL91ABNA0417164300');
    }

    public function testInvalidCountryCodeFails(): void
    {
        $result = $this->assertInvalid('XX601010012345678901');
        $this->assertHasError($result, 'country_code');
    }

    public function testInvalidChecksumFails(): void
    {
        // Valid format but wrong checksum
        $result = $this->assertInvalid('LT601010012345678900');
        $this->assertHasError($result, 'constraint', 'checksum');
    }

    public function testWrongLengthForCountryFails(): void
    {
        // LT should be 20 chars, this is too short
        $result = $this->assertInvalid('LT60101001234567890');
        $this->assertHasError($result, 'constraint', 'length');
    }

    public function testNonAlphanumericCharactersFail(): void
    {
        $this->assertInvalid('LT60-1010-0123-4567-8901');
        $this->assertInvalid('LT60_1010_0123_4567_8901');
    }

    public function testCaseInsensitive(): void
    {
        $this->assertValid('lt601010012345678901');
        $this->assertValid('Lt601010012345678901');
        $this->assertValid('lT601010012345678901');
    }

    public function testTooShortFails(): void
    {
        $this->assertInvalid('LT6010100123');  // Way too short
    }

    public function testInvalidCheckDigitsFail(): void
    {
        // Check digits must be numeric
        $result = $this->assertInvalid('LTAA1010012345678901');
        $this->assertHasError($result, 'check_digits');
    }
}
