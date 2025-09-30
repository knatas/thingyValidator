<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Immutable value object representing the result of a validation
 *
 * @package ThingyValidator
 */
readonly class ValidationResult
{
    /**
     * Create a new validation result
     *
     * @param bool $isValid Whether the validation passed
     * @param string|null $message Optional message describing the result
     * @param array<string, mixed> $errors Array of detailed error information
     * @param ValidationResultType|null $type The type of result (success, failure, warning)
     */
    public function __construct(
        public bool $isValid,
        public ?string $message = null,
        public array $errors = [],
        public ?ValidationResultType $type = null
    ) {
    }

    /**
     * Create a successful validation result
     *
     * @param string|null $message Optional success message
     * @return self
     */
    public static function success(?string $message = null): self
    {
        return new self(
            isValid: true,
            message: $message,
            errors: [],
            type: ValidationResultType::Success
        );
    }

    /**
     * Create a failed validation result
     *
     * @param string|null $message Optional failure message
     * @param array<string, mixed> $errors Detailed error information
     * @return self
     */
    public static function failure(?string $message = null, array $errors = []): self
    {
        return new self(
            isValid: false,
            message: $message,
            errors: $errors,
            type: ValidationResultType::Failure
        );
    }

    /**
     * Create a warning validation result (passes but with warnings)
     *
     * @param string|null $message Optional warning message
     * @param array<string, mixed> $errors Warning details
     * @return self
     */
    public static function warning(?string $message = null, array $errors = []): self
    {
        return new self(
            isValid: true,
            message: $message,
            errors: $errors,
            type: ValidationResultType::Warning
        );
    }
}
