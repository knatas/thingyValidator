<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Base class for validators that require parameters
 *
 * Provides parameter management and validation for validators that need
 * configuration (e.g., min/max bounds, allowed values, patterns).
 *
 * Usage:
 * ```php
 * class RangeValidator extends ParameterizedValidator
 * {
 *     protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
 *     {
 *         $min = $this->getParameter('min', 0);
 *         $max = $this->getParameter('max', 100);
 *
 *         if ($value >= $min && $value <= $max) {
 *             return $this->success();
 *         }
 *
 *         return $this->failure("Value must be between {$min} and {$max}");
 *     }
 * }
 * ```
 *
 * @package ThingyValidator
 */
abstract class ParameterizedValidator extends AbstractValidator
{
    /**
     * @var array<string, mixed> Validator parameters
     */
    protected array $parameters = [];

    /**
     * @var array<string, mixed> Required parameter definitions
     */
    protected array $requiredParameters = [];

    /**
     * Create a new parameterized validator
     *
     * @param string|null $name Optional custom name
     * @param array<string, mixed> $parameters Initial parameters
     * @throws \InvalidArgumentException If required parameters are missing
     */
    public function __construct(?string $name = null, array $parameters = [])
    {
        parent::__construct($name);
        $this->setParameters($parameters);
        $this->validateRequiredParameters();
    }

    /**
     * Set multiple parameters at once
     *
     * @param array<string, mixed> $parameters Parameters to set
     * @return self Fluent interface
     */
    public function setParameters(array $parameters): self
    {
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }

        return $this;
    }

    /**
     * Set a single parameter
     *
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @return self Fluent interface
     */
    public function setParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Get a parameter value
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed The parameter value or default
     */
    public function getParameter(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * Check if a parameter exists
     *
     * @param string $key Parameter name
     * @return bool True if parameter exists
     */
    public function hasParameter(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Get all parameters
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Remove a parameter
     *
     * @param string $key Parameter name
     * @return self Fluent interface
     */
    public function removeParameter(string $key): self
    {
        unset($this->parameters[$key]);
        return $this;
    }

    /**
     * Clear all parameters
     *
     * @return self Fluent interface
     */
    public function clearParameters(): self
    {
        $this->parameters = [];
        return $this;
    }

    /**
     * Validate that all required parameters are present
     *
     * @throws \InvalidArgumentException If required parameters are missing
     */
    protected function validateRequiredParameters(): void
    {
        foreach ($this->requiredParameters as $param => $description) {
            if (!$this->hasParameter($param)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Required parameter "%s" is missing for validator "%s": %s',
                        $param,
                        $this->getName(),
                        $description
                    )
                );
            }
        }
    }

    /**
     * Get a parameter from context or fall back to instance parameter
     *
     * Allows context to override validator parameters on a per-validation basis.
     *
     * @param string $key Parameter name
     * @param ValidationContext|null $context Optional validation context
     * @param mixed $default Default value if not found in either location
     * @return mixed The parameter value
     */
    protected function getParameterFromContext(string $key, ?ValidationContext $context, mixed $default = null): mixed
    {
        if ($context !== null && $context->has($key)) {
            return $context->get($key);
        }

        return $this->getParameter($key, $default);
    }

    /**
     * Create a new instance with different parameters
     *
     * @param array<string, mixed> $parameters New parameters
     * @return static New instance with updated parameters
     */
    public function withParameters(array $parameters): static
    {
        $clone = clone $this;
        $clone->setParameters($parameters);
        return $clone;
    }

    /**
     * Create a new instance with a single parameter changed
     *
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @return static New instance with updated parameter
     */
    public function withParameter(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->setParameter($key, $value);
        return $clone;
    }
}
