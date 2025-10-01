<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\AbstractValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * Validates International Bank Account Numbers (IBAN)
 *
 * Validates IBAN format according to ISO 13616 standard using:
 * - Country code validation (2 letters)
 * - Check digit validation (2 digits)
 * - Country-specific length validation
 * - MOD-97 checksum algorithm
 *
 * Supports all SEPA countries and major international IBAN formats.
 *
 * Usage:
 * ```php
 * $validator = new IbanValidator();
 * $result = $validator->validate('LT601010012345678901');  // Valid (Lithuania)
 * $result = $validator->validate('GB82WEST12345698765432'); // Valid (UK)
 * $result = $validator->validate('DE89370400440532013000'); // Valid (Germany)
 * $result = $validator->validate('INVALID');               // Invalid
 * ```
 *
 * @package ThingyValidator\Validators
 */
class IbanValidator extends AbstractValidator
{
    /**
     * IBAN lengths by country code (ISO 13616)
     *
     * @var array<string, int>
     */
    private const IBAN_LENGTHS = [
        'AD' => 24, 'AE' => 23, 'AL' => 28, 'AT' => 20, 'AZ' => 28,
        'BA' => 20, 'BE' => 16, 'BG' => 22, 'BH' => 22, 'BR' => 29,
        'BY' => 28, 'CH' => 21, 'CR' => 22, 'CY' => 28, 'CZ' => 24,
        'DE' => 22, 'DK' => 18, 'DO' => 28, 'EE' => 20, 'EG' => 29,
        'ES' => 24, 'FI' => 18, 'FO' => 18, 'FR' => 27, 'GB' => 22,
        'GE' => 22, 'GI' => 23, 'GL' => 18, 'GR' => 27, 'GT' => 28,
        'HR' => 21, 'HU' => 28, 'IE' => 22, 'IL' => 23, 'IS' => 26,
        'IT' => 27, 'JO' => 30, 'KW' => 30, 'KZ' => 20, 'LB' => 28,
        'LC' => 32, 'LI' => 21, 'LT' => 20, 'LU' => 20, 'LV' => 21,
        'MC' => 27, 'MD' => 24, 'ME' => 22, 'MK' => 19, 'MR' => 27,
        'MT' => 31, 'MU' => 30, 'NL' => 18, 'NO' => 15, 'PK' => 24,
        'PL' => 28, 'PS' => 29, 'PT' => 25, 'QA' => 29, 'RO' => 24,
        'RS' => 22, 'SA' => 24, 'SE' => 24, 'SI' => 19, 'SK' => 24,
        'SM' => 27, 'TN' => 24, 'TR' => 26, 'UA' => 29, 'VA' => 22,
        'VG' => 24, 'XK' => 20,
    ];

    /**
     * Create a new IBAN validator
     */
    public function __construct()
    {
        parent::__construct('iban');
        $this->errorMessage = 'Invalid IBAN format';
        $this->successMessage = 'Valid IBAN';
    }

    /**
     * Perform IBAN validation
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
    {
        // Must be a string
        if (!is_string($value)) {
            return $this->failure(
                sprintf('IBAN must be a string, %s given', gettype($value)),
                ['type' => gettype($value)]
            );
        }

        // Remove spaces and convert to uppercase
        $iban = strtoupper(str_replace(' ', '', trim($value)));

        // Cannot be empty
        if ($iban === '') {
            return $this->failure(
                'IBAN cannot be empty',
                ['value' => $value]
            );
        }

        // Must be alphanumeric
        if (!ctype_alnum($iban)) {
            return $this->failure(
                sprintf('IBAN "%s" contains invalid characters', $value),
                ['value' => $value, 'normalized' => $iban]
            );
        }

        // Must be at least 15 characters (shortest IBAN is NO - 15 chars)
        if (strlen($iban) < 15) {
            return $this->failure(
                sprintf('IBAN "%s" is too short (minimum 15 characters)', $value),
                ['value' => $value, 'length' => strlen($iban)]
            );
        }

        // Extract country code (first 2 letters)
        $countryCode = substr($iban, 0, 2);
        if (!ctype_alpha($countryCode)) {
            return $this->failure(
                sprintf('IBAN "%s" has invalid country code "%s"', $value, $countryCode),
                ['value' => $value, 'country_code' => $countryCode]
            );
        }

        // Extract check digits (positions 2-3, must be digits)
        $checkDigits = substr($iban, 2, 2);
        if (!ctype_digit($checkDigits)) {
            return $this->failure(
                sprintf('IBAN "%s" has invalid check digits "%s"', $value, $checkDigits),
                ['value' => $value, 'check_digits' => $checkDigits]
            );
        }

        // Validate country-specific length
        if (!isset(self::IBAN_LENGTHS[$countryCode])) {
            return $this->failure(
                sprintf('IBAN country code "%s" is not supported', $countryCode),
                ['value' => $value, 'country_code' => $countryCode]
            );
        }

        $expectedLength = self::IBAN_LENGTHS[$countryCode];
        if (strlen($iban) !== $expectedLength) {
            return $this->failure(
                sprintf(
                    'IBAN "%s" has invalid length for country %s (expected %d, got %d)',
                    $value,
                    $countryCode,
                    $expectedLength,
                    strlen($iban)
                ),
                [
                    'value' => $value,
                    'country_code' => $countryCode,
                    'expected_length' => $expectedLength,
                    'actual_length' => strlen($iban),
                    'constraint' => 'length'
                ]
            );
        }

        // Validate checksum using MOD-97 algorithm
        if (!$this->validateChecksum($iban)) {
            return $this->failure(
                sprintf('IBAN "%s" has invalid checksum', $value),
                [
                    'value' => $value,
                    'country_code' => $countryCode,
                    'check_digits' => $checkDigits,
                    'constraint' => 'checksum'
                ]
            );
        }

        // Success
        return $this->success(
            sprintf('IBAN "%s" is valid', $value),
            [
                'value' => $value,
                'normalized' => $iban,
                'country_code' => $countryCode,
                'check_digits' => $checkDigits,
                'length' => strlen($iban)
            ]
        );
    }

    /**
     * Validate IBAN checksum using MOD-97 algorithm
     *
     * @param string $iban The IBAN to validate (already normalized)
     * @return bool True if checksum is valid
     */
    private function validateChecksum(string $iban): bool
    {
        // Move first 4 characters to the end
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // Replace letters with numbers (A=10, B=11, ..., Z=35)
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                // A=10, B=11, etc.
                $numeric .= (string) (ord($char) - ord('A') + 10);
            } else {
                $numeric .= $char;
            }
        }

        // Perform MOD-97 calculation
        // For large numbers, we need to process in chunks
        $remainder = 0;
        $numericLength = strlen($numeric);

        for ($i = 0; $i < $numericLength; $i++) {
            $remainder = ($remainder * 10 + (int) $numeric[$i]) % 97;
        }

        // Valid IBAN should have remainder of 1
        return $remainder === 1;
    }
}
