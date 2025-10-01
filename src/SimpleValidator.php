<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Simple validator for basic boolean validations
 *
 * Wraps a callable that returns a boolean value, providing a simple
 * way to create validators without extending AbstractValidator.
 *
 * Usage:
 * ```php
 * $isEven = new SimpleValidator(
 *     'even',
 *     fn($value) => is_int($value) && $value % 2 === 0,
 *     'Value must be an even number'
 * );
 * ```
 *
 * @package ThingyValidator
 */
class SimpleValidator extends AbstractValidator
{
    /**
     * @var callable(mixed, ?ValidationContext): bool The validation callable
     */
    private $validationCallable;

    /**
     * Create a new simple validator
     *
     * @param string $name The validator name
     * @param callable(mixed, ?ValidationContext): bool $validationCallable Callable that returns boolean
     * @param string|null $errorMessage Optional error message for failures
     * @param string|null $successMessage Optional success message
     */
    public function __construct(
        string $name,
        callable $validationCallable,
        ?string $errorMessage = null,
        ?string $successMessage = null
    ) {
        parent::__construct($name);
        $this->validationCallable = $validationCallable;

        if ($errorMessage !== null) {
            $this->errorMessage = $errorMessage;
        }

        if ($successMessage !== null) {
            $this->successMessage = $successMessage;
        }
    }

    /**
     * Perform the validation using the provided callable
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
    {
        $callable = $this->validationCallable;

        // Support both single-parameter and two-parameter callables
        $reflection = new \ReflectionFunction($callable(...));
        $paramCount = $reflection->getNumberOfParameters();

        try {
            $isValid = $paramCount >= 2
                ? $callable($value, $context)
                : $callable($value);

            return $isValid
                ? $this->success()
                : $this->failure();
        } catch (\Throwable $e) {
            return $this->failure(
                'Validation error: ' . $e->getMessage(),
                ['exception' => $e->getMessage(), 'type' => get_class($e)]
            );
        }
    }

    /**
     * Create a simple validator from a callable
     *
     * Static factory method for convenient validator creation.
     *
     * @param string $name The validator name
     * @param callable(mixed, ?ValidationContext): bool $validationCallable Callable that returns boolean
     * @param string|null $errorMessage Optional error message
     * @param string|null $successMessage Optional success message
     * @return self
     */
    public static function fromCallable(
        string $name,
        callable $validationCallable,
        ?string $errorMessage = null,
        ?string $successMessage = null
    ): self {
        return new self($name, $validationCallable, $errorMessage, $successMessage);
    }
}
