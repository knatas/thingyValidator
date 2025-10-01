<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Abstract base class for validators providing common functionality
 *
 * Provides template method pattern for validation with pre/post processing hooks
 * and common error message formatting.
 *
 * @package ThingyValidator
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var string The name of this validator
     */
    protected string $name;

    /**
     * @var string Default error message template
     */
    protected string $errorMessage = 'Validation failed';

    /**
     * @var string Default success message template
     */
    protected string $successMessage = 'Validation passed';

    /**
     * Create a new validator instance
     *
     * @param string|null $name Optional custom name (defaults to class-based name)
     */
    public function __construct(?string $name = null)
    {
        $this->name = $name ?? $this->getDefaultName();
    }

    /**
     * Get the unique name of this validator
     *
     * @return string The validator name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Validate a value with optional context
     *
     * Template method that calls doValidate() for actual validation logic.
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    public function validate(mixed $value, ?ValidationContext $context = null): ValidationResult
    {
        // Pre-validation hook
        $value = $this->preValidate($value, $context);

        // Perform actual validation
        $result = $this->doValidate($value, $context);

        // Post-validation hook
        return $this->postValidate($result, $value, $context);
    }

    /**
     * Perform the actual validation logic (must be implemented by subclasses)
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    abstract protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult;

    /**
     * Pre-validation hook for value transformation or normalization
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return mixed The transformed value
     */
    protected function preValidate(mixed $value, ?ValidationContext $context): mixed
    {
        return $value;
    }

    /**
     * Post-validation hook for result transformation
     *
     * @param ValidationResult $result The validation result
     * @param mixed $value The original value
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The transformed result
     */
    protected function postValidate(ValidationResult $result, mixed $value, ?ValidationContext $context): ValidationResult
    {
        return $result;
    }

    /**
     * Create a success result with optional message
     *
     * @param string|null $message Optional success message
     * @return ValidationResult
     */
    protected function success(?string $message = null): ValidationResult
    {
        return ValidationResult::success($message ?? $this->successMessage);
    }

    /**
     * Create a failure result with optional message and errors
     *
     * @param string|null $message Optional failure message
     * @param array<string, mixed> $errors Detailed error information
     * @return ValidationResult
     */
    protected function failure(?string $message = null, array $errors = []): ValidationResult
    {
        return ValidationResult::failure($message ?? $this->errorMessage, $errors);
    }

    /**
     * Create a warning result with optional message and errors
     *
     * @param string|null $message Optional warning message
     * @param array<string, mixed> $errors Warning details
     * @return ValidationResult
     */
    protected function warning(?string $message = null, array $errors = []): ValidationResult
    {
        return ValidationResult::warning($message ?? $this->successMessage, $errors);
    }

    /**
     * Get the default validator name based on class name
     *
     * Converts class name from PascalCase to lowercase (e.g., EmailValidator -> email)
     *
     * @return string The default validator name
     */
    protected function getDefaultName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();

        // Remove "Validator" suffix if present
        if (str_ends_with($className, 'Validator')) {
            $className = substr($className, 0, -9);
        }

        // Convert from PascalCase to lowercase
        return strtolower($className);
    }

    /**
     * Format an error message with placeholders
     *
     * @param string $template Message template with {placeholder} format
     * @param array<string, mixed> $data Data to replace placeholders
     * @return string The formatted message
     */
    protected function formatMessage(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $template = str_replace($placeholder, (string) $value, $template);
        }

        return $template;
    }
}
