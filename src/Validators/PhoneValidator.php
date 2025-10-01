<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\AbstractValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * Validates international phone numbers
 *
 * Validates phone numbers in E.164 format and common international formats.
 * Supports optional country code, various separators, and basic format checking.
 *
 * Accepted formats:
 * - E.164: +1234567890
 * - With spaces: +1 234 567 890
 * - With dashes: +1-234-567-890
 * - With parentheses: +1 (234) 567-890
 * - Without plus: 1234567890
 * - Local format: (234) 567-890
 *
 * Usage:
 * ```php
 * $validator = new PhoneValidator();
 * $result = $validator->validate('+37061234567');    // Valid (E.164)
 * $result = $validator->validate('+1 (555) 123-4567'); // Valid
 * $result = $validator->validate('555-1234');         // Valid (local)
 * $result = $validator->validate('abc');              // Invalid
 * ```
 *
 * @package ThingyValidator\Validators
 */
class PhoneValidator extends AbstractValidator
{
    /**
     * Minimum phone number length (after removing non-digits)
     */
    private const MIN_LENGTH = 7;

    /**
     * Maximum phone number length (after removing non-digits)
     */
    private const MAX_LENGTH = 15;

    /**
     * Create a new phone validator
     */
    public function __construct()
    {
        parent::__construct('phone');
        $this->errorMessage = 'Invalid phone number format';
        $this->successMessage = 'Valid phone number';
    }

    /**
     * Perform phone number validation
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
                sprintf('Phone number must be a string, %s given', gettype($value)),
                ['type' => gettype($value)]
            );
        }

        // Cannot be empty
        $value = trim($value);
        if ($value === '') {
            return $this->failure(
                'Phone number cannot be empty',
                ['value' => $value]
            );
        }

        // Extract only digits and plus sign for validation
        $digitsOnly = preg_replace('/[^0-9+]/', '', $value);

        // Check if there's at least one digit
        if (!preg_match('/\d/', $digitsOnly)) {
            return $this->failure(
                sprintf('Phone number "%s" contains no digits', $value),
                ['value' => $value, 'extracted' => $digitsOnly]
            );
        }

        // Plus sign can only appear at the beginning
        $plusCount = substr_count($digitsOnly, '+');
        if ($plusCount > 1 || ($plusCount === 1 && $digitsOnly[0] !== '+')) {
            return $this->failure(
                sprintf('Phone number "%s" has invalid plus sign placement', $value),
                ['value' => $value, 'extracted' => $digitsOnly]
            );
        }

        // Remove plus for length check
        $digitsForLength = str_replace('+', '', $digitsOnly);
        $length = strlen($digitsForLength);

        // Check length constraints
        if ($length < self::MIN_LENGTH) {
            return $this->failure(
                sprintf(
                    'Phone number "%s" is too short (minimum %d digits, got %d)',
                    $value,
                    self::MIN_LENGTH,
                    $length
                ),
                [
                    'value' => $value,
                    'length' => $length,
                    'min_length' => self::MIN_LENGTH,
                    'constraint' => 'min_length'
                ]
            );
        }

        if ($length > self::MAX_LENGTH) {
            return $this->failure(
                sprintf(
                    'Phone number "%s" is too long (maximum %d digits, got %d)',
                    $value,
                    self::MAX_LENGTH,
                    $length
                ),
                [
                    'value' => $value,
                    'length' => $length,
                    'max_length' => self::MAX_LENGTH,
                    'constraint' => 'max_length'
                ]
            );
        }

        // Check for valid format patterns
        $validPatterns = [
            // E.164 format: +[country code][number]
            '/^\+[1-9]\d{1,14}$/',
            // With spaces: +1 234 567 890
            '/^\+[1-9][\d\s]{1,18}$/',
            // With dashes: +1-234-567-890
            '/^\+[1-9][\d\-\s]{1,18}$/',
            // With parentheses: +1 (234) 567-890
            '/^\+?[1-9]?[\d\s\(\)\-]{6,20}$/',
            // Local format without country code
            '/^[\d\s\(\)\-]{7,20}$/',
        ];

        $matchesPattern = false;
        foreach ($validPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $matchesPattern = true;
                break;
            }
        }

        if (!$matchesPattern) {
            return $this->failure(
                sprintf('Phone number "%s" does not match valid format patterns', $value),
                ['value' => $value, 'digits' => $digitsOnly]
            );
        }

        // Success
        return $this->success(
            sprintf('Phone number "%s" is valid', $value),
            [
                'value' => $value,
                'normalized' => $digitsOnly,
                'length' => $length,
                'has_country_code' => str_starts_with($digitsOnly, '+')
            ]
        );
    }
}
