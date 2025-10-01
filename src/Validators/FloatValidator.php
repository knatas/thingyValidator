<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\ParameterizedValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * Validates floating-point numbers with optional precision and range constraints
 *
 * Checks if a value is a valid float and optionally validates precision (decimal places),
 * and min/max range. Strict mode rejects numeric strings.
 *
 * Usage:
 * ```php
 * // Basic float validation
 * $validator = new FloatValidator();
 * $result = $validator->validate(3.14);      // Valid
 * $result = $validator->validate(42);        // Valid (integers are valid floats)
 * $result = $validator->validate('3.14');    // Valid (numeric string, non-strict)
 *
 * // With precision constraint (max 2 decimal places)
 * $validator = new FloatValidator(['precision' => 2]);
 * $result = $validator->validate(3.14);      // Valid
 * $result = $validator->validate(3.145);     // Invalid (too many decimals)
 *
 * // With range constraints
 * $validator = new FloatValidator(['min' => 0.0, 'max' => 100.0]);
 * $result = $validator->validate(50.5);      // Valid
 * $result = $validator->validate(150.0);     // Invalid (out of range)
 *
 * // Strict mode (reject numeric strings)
 * $validator = new FloatValidator(['strict' => true]);
 * $result = $validator->validate('3.14');    // Invalid (string)
 * ```
 *
 * @package ThingyValidator\Validators
 */
class FloatValidator extends ParameterizedValidator
{
    /**
     * Create a new float validator
     *
     * @param array<string, mixed> $parameters Optional parameters:
     *   - 'min' (float): Minimum allowed value (inclusive)
     *   - 'max' (float): Maximum allowed value (inclusive)
     *   - 'precision' (int): Maximum number of decimal places
     *   - 'strict' (bool): Reject numeric strings (default: false)
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct('float', $parameters);
        $this->errorMessage = 'Value must be a floating-point number';
        $this->successMessage = 'Value is a valid float';
    }

    /**
     * Perform float validation with optional precision and range checks
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
        $precision = $this->getParameterFromContext('precision', $context);

        // Strict mode: must be actual float or int type
        if ($strict) {
            if (!is_float($value) && !is_int($value)) {
                return $this->failure(
                    sprintf('Value "%s" is not a float (strict mode)', $this->formatValue($value)),
                    ['type' => gettype($value), 'value' => $value, 'strict' => true]
                );
            }
            $floatValue = (float) $value;
        } else {
            // Non-strict: accept numeric strings
            if (!is_numeric($value)) {
                return $this->failure(
                    sprintf('Value "%s" is not numeric', $this->formatValue($value)),
                    ['type' => gettype($value), 'value' => $value]
                );
            }
            $floatValue = (float) $value;
        }

        // Precision validation (check decimal places)
        if ($precision !== null && $precision >= 0) {
            $actualPrecision = $this->getDecimalPlaces($floatValue);
            if ($actualPrecision > $precision) {
                return $this->failure(
                    sprintf(
                        'Value %s has %d decimal places, maximum allowed is %d',
                        $floatValue,
                        $actualPrecision,
                        $precision
                    ),
                    [
                        'value' => $floatValue,
                        'precision' => $precision,
                        'actual_precision' => $actualPrecision,
                        'constraint' => 'precision'
                    ]
                );
            }
        }

        // Range validation
        if ($min !== null && $floatValue < $min) {
            return $this->failure(
                sprintf('Value %s is below minimum %s', $floatValue, $min),
                ['value' => $floatValue, 'min' => $min, 'constraint' => 'min']
            );
        }

        if ($max !== null && $floatValue > $max) {
            return $this->failure(
                sprintf('Value %s is above maximum %s', $floatValue, $max),
                ['value' => $floatValue, 'max' => $max, 'constraint' => 'max']
            );
        }

        // Build success message with constraints info
        $message = sprintf('Value %s is a valid float', $floatValue);
        $constraints = [];

        if ($min !== null) {
            $constraints[] = "min: {$min}";
        }
        if ($max !== null) {
            $constraints[] = "max: {$max}";
        }
        if ($precision !== null) {
            $constraints[] = "precision: {$precision}";
        }

        if (!empty($constraints)) {
            $message .= ' (' . implode(', ', $constraints) . ')';
        }

        return $this->success($message, [
            'value' => $floatValue,
            'min' => $min,
            'max' => $max,
            'precision' => $precision,
            'actual_precision' => $precision !== null ? $this->getDecimalPlaces($floatValue) : null,
            'strict' => $strict
        ]);
    }

    /**
     * Get the number of decimal places in a float
     *
     * @param float $value The float value
     * @return int Number of decimal places
     */
    private function getDecimalPlaces(float $value): int
    {
        // Handle scientific notation and very small/large numbers
        $stringValue = rtrim(sprintf('%.14F', $value), '0');

        // Find decimal point
        $decimalPos = strpos($stringValue, '.');
        if ($decimalPos === false) {
            return 0;
        }

        // Count digits after decimal point (excluding trailing zeros)
        $afterDecimal = substr($stringValue, $decimalPos + 1);
        return strlen(rtrim($afterDecimal, '0'));
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
