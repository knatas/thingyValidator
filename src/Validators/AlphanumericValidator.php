<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\AbstractValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * Alphanumeric characters validator
 *
 * Validates that a string contains only alphanumeric characters (letters and numbers).
 * Supports Unicode characters and optional whitespace/special characters.
 *
 * Features:
 * - ASCII alphanumeric validation (default)
 * - Unicode letter and number validation (optional)
 * - Optional whitespace allowance
 * - Optional underscore/dash allowance
 *
 * Usage:
 * ```php
 * $validator = new AlphanumericValidator();
 * $result = $validator->validate('Hello123');   // Valid
 * $result = $validator->validate('Hello-World'); // Invalid
 *
 * // Allow hyphens and underscores
 * $context = new ValidationContext(['allow_hyphens' => true, 'allow_underscores' => true]);
 * $result = $validator->validate('Hello_World-123', $context); // Valid
 * ```
 *
 * @package ThingyValidator\Validators
 */
class AlphanumericValidator extends AbstractValidator
{
    protected string $errorMessage = 'Value must contain only alphanumeric characters';
    protected string $successMessage = 'Valid alphanumeric string';

    /**
     * Perform alphanumeric validation
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
        $allowUnderscores = $context?->get('allow_underscores', false) ?? false;
        $allowHyphens = $context?->get('allow_hyphens', false) ?? false;

        // Validate based on configuration
        if ($allowUnicode) {
            $isValid = $this->validateUnicode($value, $allowSpaces, $allowUnderscores, $allowHyphens);
        } else {
            $isValid = $this->validateAscii($value, $allowSpaces, $allowUnderscores, $allowHyphens);
        }

        if (!$isValid) {
            $errors = [
                'value' => $value,
                'allow_spaces' => $allowSpaces,
                'allow_unicode' => $allowUnicode,
                'allow_underscores' => $allowUnderscores,
                'allow_hyphens' => $allowHyphens,
            ];

            return $this->failure(
                $this->buildErrorMessage($allowSpaces, $allowUnicode, $allowUnderscores, $allowHyphens),
                $errors
            );
        }

        return $this->success();
    }

    /**
     * Validate ASCII alphanumeric characters
     *
     * @param string $value The value to validate
     * @param bool $allowSpaces Whether to allow spaces
     * @param bool $allowUnderscores Whether to allow underscores
     * @param bool $allowHyphens Whether to allow hyphens
     * @return bool True if valid
     */
    private function validateAscii(
        string $value,
        bool $allowSpaces,
        bool $allowUnderscores,
        bool $allowHyphens
    ): bool {
        // Build pattern
        $pattern = '/^[a-zA-Z0-9';

        if ($allowSpaces) {
            $pattern .= ' ';
        }

        if ($allowUnderscores) {
            $pattern .= '_';
        }

        if ($allowHyphens) {
            $pattern .= '\-';
        }

        $pattern .= ']+$/';

        return preg_match($pattern, $value) === 1;
    }

    /**
     * Validate Unicode alphanumeric characters
     *
     * @param string $value The value to validate
     * @param bool $allowSpaces Whether to allow spaces
     * @param bool $allowUnderscores Whether to allow underscores
     * @param bool $allowHyphens Whether to allow hyphens
     * @return bool True if valid
     */
    private function validateUnicode(
        string $value,
        bool $allowSpaces,
        bool $allowUnderscores,
        bool $allowHyphens
    ): bool {
        // Build pattern for Unicode letters and numbers
        $pattern = '/^[\p{L}\p{N}';

        if ($allowSpaces) {
            $pattern .= '\s';
        }

        if ($allowUnderscores) {
            $pattern .= '_';
        }

        if ($allowHyphens) {
            $pattern .= '\-';
        }

        $pattern .= ']+$/u';

        return preg_match($pattern, $value) === 1;
    }

    /**
     * Build contextual error message
     *
     * @param bool $allowSpaces Whether spaces are allowed
     * @param bool $allowUnicode Whether unicode is allowed
     * @param bool $allowUnderscores Whether underscores are allowed
     * @param bool $allowHyphens Whether hyphens are allowed
     * @return string Error message
     */
    private function buildErrorMessage(
        bool $allowSpaces,
        bool $allowUnicode,
        bool $allowUnderscores,
        bool $allowHyphens
    ): string {
        $message = 'Value must contain only ';

        if ($allowUnicode) {
            $message .= 'alphanumeric characters';
        } else {
            $message .= 'ASCII letters and numbers (a-z, A-Z, 0-9)';
        }

        $extras = [];
        if ($allowSpaces) {
            $extras[] = 'spaces';
        }
        if ($allowUnderscores) {
            $extras[] = 'underscores';
        }
        if ($allowHyphens) {
            $extras[] = 'hyphens';
        }

        if (!empty($extras)) {
            $message .= ', ' . implode(', ', $extras);
        }

        return $message;
    }
}
