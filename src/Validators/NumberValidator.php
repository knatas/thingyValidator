<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\AbstractValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * Validates numeric values (integers and floats)
 *
 * Checks if a value is numeric, accepting both integers and floating-point numbers.
 * Uses PHP's is_numeric() internally which accepts strings that represent numbers.
 *
 * Usage:
 * ```php
 * $validator = new NumberValidator();
 * $result = $validator->validate(42);        // Valid
 * $result = $validator->validate(3.14);      // Valid
 * $result = $validator->validate('123');     // Valid (numeric string)
 * $result = $validator->validate('abc');     // Invalid
 * ```
 *
 * @package ThingyValidator\Validators
 */
class NumberValidator extends AbstractValidator
{
    /**
     * Create a new number validator
     */
    public function __construct()
    {
        parent::__construct('number');
        $this->errorMessage = 'Value must be numeric';
        $this->successMessage = 'Value is numeric';
    }

    /**
     * Perform numeric validation
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
    {
        // Check if value is numeric (int, float, or numeric string)
        if (!is_numeric($value)) {
            return $this->failure(
                sprintf('Value "%s" is not numeric', $this->formatValue($value)),
                ['type' => gettype($value), 'value' => $value]
            );
        }

        return $this->success(
            sprintf('Value %s is numeric', $this->formatValue($value)),
            ['type' => gettype($value), 'value' => $value]
        );
    }

    /**
     * Format a value for display in error messages
     *
     * @param mixed $value The value to format
     * @return string Formatted value
     */
    private function formatValue(mixed $value): string
    {
        if (is_string($value)) {
            return '"' . $value . '"';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_object($value)) {
            return 'object(' . get_class($value) . ')';
        }

        return (string) $value;
    }
}
