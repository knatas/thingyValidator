<?php

declare(strict_types=1);

namespace ThingyValidator\Tests\Validators;

use ThingyValidator\Tests\ValidatorTestCase;
use ThingyValidator\Tests\DataProviders\CommonDataProvider;
use ThingyValidator\Validators\EmailValidator;
use ThingyValidator\ValidatorInterface;

/**
 * Test case for EmailValidator
 *
 * @package ThingyValidator\Tests\Validators
 */
class EmailValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ValidatorInterface
    {
        return new EmailValidator();
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::validEmails
     */
    public function testValidEmailsPass(string $email): void
    {
        $result = $this->assertValid($email);
        $this->assertMessageContains($result, 'Valid');
    }

    /**
     * @dataProvider \ThingyValidator\Tests\DataProviders\CommonDataProvider::invalidEmails
     */
    public function testInvalidEmailsFail(string $email): void
    {
        $result = $this->assertInvalid($email);
        $this->assertNotNull($result->message);
    }

    public function testEmptyEmailFails(): void
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
        $this->assertEquals('email', $this->validator->getName());
    }
}
