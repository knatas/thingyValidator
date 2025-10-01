<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\AbstractValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * Alphabetic characters validator
 *
 * Validates that a string contains only alphabetic characters (letters).
 * Supports Unicode characters and optional whitespace/diacritics.
 *
 * Features:
 * - ASCII alphabetic validation (default)
 * - Unicode letter validation (optional)
 * - Optional whitespace allowance
 * - Diacritics support (optional)
 *
 * Usage:
 * ```php
 * $validator = new AlphaValidator();
 * $result = $validator->validate('HelloWorld'); // Valid
 * $result = $validator->validate('Hello123');   // Invalid
 *
 * // Allow spaces
 * $context = new ValidationContext(['allow_spaces' => true]);
 * $result = $validator->validate('Hello World', $context); // Valid
 * ```
 *
 * @package ThingyValidator\Validators
 */
class AlphaValidator extends AbstractValidator
{
    protected string $errorMessage = 'Value must contain only alphabetic characters';
    protected string $successMessage = 'Valid alphabetic string';

    /**
     * Perform alphabetic validation
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
    {
        // Type check
        if (!is_string($value)) {
            return $this->failure('Value must be a string', ['type' => gettype($value)]);
        }

        // Empty check
        if ($value === '') {
            return $this->failure('Value cannot be empty');
        }

        // Get configuration from context
        $allowSpaces = $context?->get('allow_spaces', false) ?? false;
        $allowUnicode = $context?->get('allow_unicode', false) ?? false;
        $allowDiacritics = $context?->get('allow_diacritics', true) ?? true;

        // Validate based on configuration
        if ($allowUnicode) {
            $isValid = $this->validateUnicode($value, $allowSpaces, $allowDiacritics);
        } else {
            $isValid = $this->validateAscii($value, $allowSpaces);
        }

        if (!$isValid) {
            $errors = [
                'value' => $value,
                'allow_spaces' => $allowSpaces,
                'allow_unicode' => $allowUnicode,
            ];

            return $this->failure(
                $this->buildErrorMessage($allowSpaces, $allowUnicode),
                $errors
            );
        }

        return $this->success();
    }

    /**
     * Validate ASCII alphabetic characters
     *
     * @param string $value The value to validate
     * @param bool $allowSpaces Whether to allow spaces
     * @return bool True if valid
     */
    private function validateAscii(string $value, bool $allowSpaces): bool
    {
        if ($allowSpaces) {
            // Allow letters and spaces
            return ctype_alpha(str_replace(' ', '', $value)) && strlen(str_replace(' ', '', $value)) > 0;
        }

        // Only letters
        return ctype_alpha($value);
    }

    /**
     * Validate Unicode alphabetic characters
     *
     * @param string $value The value to validate
     * @param bool $allowSpaces Whether to allow spaces
     * @param bool $allowDiacritics Whether to allow diacritics
     * @return bool True if valid
     */
    private function validateUnicode(string $value, bool $allowSpaces, bool $allowDiacritics): bool
    {
        // Build pattern based on options
        $pattern = '/^[\p{L}';

        if ($allowDiacritics) {
            $pattern .= '\p{M}'; // Mark category (combining diacritics)
        }

        if ($allowSpaces) {
            $pattern .= '\s'; // Whitespace
        }

        $pattern .= ']+$/u';

        return preg_match($pattern, $value) === 1;
    }

    /**
     * Build contextual error message
     *
     * @param bool $allowSpaces Whether spaces are allowed
     * @param bool $allowUnicode Whether unicode is allowed
     * @return string Error message
     */
    private function buildErrorMessage(bool $allowSpaces, bool $allowUnicode): string
    {
        $message = 'Value must contain only ';

        if ($allowUnicode) {
            $message .= 'alphabetic characters';
        } else {
            $message .= 'ASCII letters (a-z, A-Z)';
        }

        if ($allowSpaces) {
            $message .= ' and spaces';
        }

        return $message;
    }
}
