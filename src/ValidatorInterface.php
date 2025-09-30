<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Contract for all validators (built-in and custom)
 *
 * @package ThingyValidator
 */
interface ValidatorInterface
{
    /**
     * Validate a value with optional context
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    public function validate(mixed $value, ?ValidationContext $context = null): ValidationResult;

    /**
     * Get the unique name of this validator
     *
     * @return string The validator name (e.g., 'email', 'phone', 'url')
     */
    public function getName(): string;
}
