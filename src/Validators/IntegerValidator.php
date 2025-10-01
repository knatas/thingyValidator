<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\ParameterizedValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * Validates integer values with optional range constraints
 *
 * Checks if a value is an integer and optionally validates it falls within
 * a specified min/max range. Strict mode rejects numeric strings.
 *
 * Usage:
 * ```php
 * // Basic integer validation
 * $validator = new IntegerValidator();
 * $result = $validator->validate(42);        // Valid
 * $result = $validator->validate(3.14);      // Invalid (float)
 * $result = $validator->validate('123');     // Valid (numeric string, non-strict)
 *
 * // With range constraints
 * $validator = new IntegerValidator(['min' => 1, 'max' => 100]);
 * $result = $validator->validate(50);        // Valid
 * $result = $validator->validate(150);       // Invalid (out of range)
 *
 * // Strict mode (rejects numeric strings)
 * $validator = new IntegerValidator(['strict' => true]);
 * $result = $validator->validate('123');     // Invalid (string)
 * ```
 *
 * @package ThingyValidator\Validators
 */
class IntegerValidator extends ParameterizedValidator
{
    /**
     * Create a new integer validator
     *
     * @param array<string, mixed> $parameters Optional parameters:
     *   - 'min' (int): Minimum allowed value (inclusive)
     *   - 'max' (int): Maximum allowed value (inclusive)
     *   - 'strict' (bool): Reject numeric strings (default: false)
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct('integer', $parameters);
        $this->errorMessage = 'Value must be an integer';
        $this->successMessage = 'Value is a valid integer';
    }

    /**
     * Perform integer validation with optional range check
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
    {
        $strict = $this->getParameterFromContext('strict', $context, false);
        $min = $this->getParameterFromContext('min', $context);
        $max = $this->getParameterFromContext('max', $context);

        // Strict mode: must be actual integer type
        if ($strict) {
            if (!is_int($value)) {
                return $this->failure(
                    sprintf('Value "%s" is not an integer (strict mode)', $this->formatValue($value)),
                    ['type' => gettype($value), 'value' => $value, 'strict' => true]
                );
            }
        } else {
            // Non-strict: accept numeric strings that represent integers
            if (!is_numeric($value)) {
                return $this->failure(
                    sprintf('Value "%s" is not numeric', $this->formatValue($value)),
                    ['type' => gettype($value), 'value' => $value]
                );
            }

            // Check if numeric value is actually an integer (no decimal part)
            $numericValue = is_string($value) ? floatval($value) : $value;
            if (floor($numericValue) !== $numericValue) {
                return $this->failure(
                    sprintf('Value %s is not an integer (has decimal part)', $this->formatValue($value)),
                    ['type' => gettype($value), 'value' => $value, 'decimal_part' => true]
                );
            }

            $value = (int) $numericValue;
        }

        // Range validation
        if ($min !== null && $value < $min) {
            return $this->failure(
                sprintf('Value %d is below minimum %d', $value, $min),
                ['value' => $value, 'min' => $min, 'constraint' => 'min']
            );
        }

        if ($max !== null && $value > $max) {
            return $this->failure(
                sprintf('Value %d is above maximum %d', $value, $max),
                ['value' => $value, 'max' => $max, 'constraint' => 'max']
            );
        }

        // Build success message with range info
        $message = sprintf('Value %d is a valid integer', $value);
        if ($min !== null || $max !== null) {
            $rangeInfo = [];
            if ($min !== null) {
                $rangeInfo[] = "min: {$min}";
            }
            if ($max !== null) {
                $rangeInfo[] = "max: {$max}";
            }
            $message .= ' (' . implode(', ', $rangeInfo) . ')';
        }

        return $this->success($message, [
            'value' => $value,
            'min' => $min,
            'max' => $max,
            'strict' => $strict
        ]);
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
