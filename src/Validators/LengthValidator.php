<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\ParameterizedValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * String length validator
 *
 * Validates that a string's length is within specified minimum and maximum bounds.
 * Uses multibyte string length for proper Unicode support.
 *
 * Features:
 * - Minimum length validation
 * - Maximum length validation
 * - Exact length validation
 * - Multibyte Unicode support
 * - Byte length validation (optional)
 *
 * Usage:
 * ```php
 * // Min/max length via constructor
 * $validator = new LengthValidator(null, ['min' => 5, 'max' => 20]);
 * $result = $validator->validate('Hello World');
 *
 * // Min/max via context (overrides constructor)
 * $context = new ValidationContext(['min' => 3, 'max' => 10]);
 * $result = $validator->validate('Test', $context);
 *
 * // Exact length
 * $validator = new LengthValidator(null, ['exact' => 10]);
 * $result = $validator->validate('ExactlyTen');
 * ```
 *
 * @package ThingyValidator\Validators
 */
class LengthValidator extends ParameterizedValidator
{
    protected string $errorMessage = 'String length is invalid';
    protected string $successMessage = 'String length is valid';

    /**
     * Perform length validation
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

        // Get length configuration (context overrides instance parameters)
        $min = $this->getParameterFromContext('min', $context, null);
        $max = $this->getParameterFromContext('max', $context, null);
        $exact = $this->getParameterFromContext('exact', $context, null);
        $useBytes = $this->getParameterFromContext('use_bytes', $context, false);

        // Calculate length
        $length = $useBytes ? strlen($value) : mb_strlen($value);

        // Exact length check takes precedence
        if ($exact !== null) {
            if ($length !== $exact) {
                return $this->failure(
                    $this->formatMessage(
                        'String must be exactly {exact} characters long',
                        ['exact' => $exact]
                    ),
                    [
                        'length' => $length,
                        'exact' => $exact,
                        'use_bytes' => $useBytes,
                    ]
                );
            }

            return $this->success();
        }

        // Minimum length check
        if ($min !== null && $length < $min) {
            return $this->failure(
                $this->formatMessage(
                    'String must be at least {min} characters long',
                    ['min' => $min]
                ),
                [
                    'length' => $length,
                    'min' => $min,
                    'use_bytes' => $useBytes,
                ]
            );
        }

        // Maximum length check
        if ($max !== null && $length > $max) {
            return $this->failure(
                $this->formatMessage(
                    'String must be at most {max} characters long',
                    ['max' => $max]
                ),
                [
                    'length' => $length,
                    'max' => $max,
                    'use_bytes' => $useBytes,
                ]
            );
        }

        // Range validation
        if ($min !== null && $max !== null) {
            return $this->success(
                $this->formatMessage(
                    'String length is within range ({min}-{max})',
                    ['min' => $min, 'max' => $max]
                )
            );
        }

        return $this->success();
    }

    /**
     * Create a validator for minimum length only
     *
     * @param int $min Minimum length
     * @param bool $useBytes Use byte length instead of character length
     * @return self
     */
    public static function min(int $min, bool $useBytes = false): self
    {
        return new self(null, ['min' => $min, 'use_bytes' => $useBytes]);
    }

    /**
     * Create a validator for maximum length only
     *
     * @param int $max Maximum length
     * @param bool $useBytes Use byte length instead of character length
     * @return self
     */
    public static function max(int $max, bool $useBytes = false): self
    {
        return new self(null, ['max' => $max, 'use_bytes' => $useBytes]);
    }

    /**
     * Create a validator for exact length
     *
     * @param int $exact Exact length required
     * @param bool $useBytes Use byte length instead of character length
     * @return self
     */
    public static function exact(int $exact, bool $useBytes = false): self
    {
        return new self(null, ['exact' => $exact, 'use_bytes' => $useBytes]);
    }

    /**
     * Create a validator for length range
     *
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @param bool $useBytes Use byte length instead of character length
     * @return self
     */
    public static function between(int $min, int $max, bool $useBytes = false): self
    {
        return new self(null, ['min' => $min, 'max' => $max, 'use_bytes' => $useBytes]);
    }
}
