<?php

declare(strict_types=1);

namespace ThingyValidator;

use RuntimeException;

/**
 * Registry for managing validator instances
 *
 * Provides centralized registration, retrieval, and management
 * of validator instances with singleton pattern.
 *
 * @package ThingyValidator
 */
class ValidatorRegistry
{
    /**
     * @var array<string, ValidatorInterface> Registered validators
     */
    private array $validators = [];

    /**
     * @var self|null Singleton instance
     */
    private static ?self $instance = null;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
    }

    /**
     * Get the singleton registry instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register a validator
     *
     * @param ValidatorInterface $validator The validator to register
     * @param bool $overwrite Whether to overwrite existing validator with same name
     * @return self Fluent interface
     * @throws RuntimeException If validator already exists and overwrite is false
     */
    public function register(ValidatorInterface $validator, bool $overwrite = false): self
    {
        $name = $validator->getName();

        if (!$overwrite && $this->has($name)) {
            throw new RuntimeException(
                sprintf('Validator "%s" is already registered. Use overwrite=true to replace.', $name)
            );
        }

        $this->validators[$name] = $validator;
        return $this;
    }

    /**
     * Get a registered validator by name
     *
     * @param string $name The validator name
     * @return ValidatorInterface|null The validator or null if not found
     */
    public function get(string $name): ?ValidatorInterface
    {
        return $this->validators[$name] ?? null;
    }

    /**
     * Check if a validator is registered
     *
     * @param string $name The validator name
     * @return bool True if the validator exists
     */
    public function has(string $name): bool
    {
        return isset($this->validators[$name]);
    }

    /**
     * Unregister a validator
     *
     * @param string $name The validator name to remove
     * @return self Fluent interface
     */
    public function unregister(string $name): self
    {
        unset($this->validators[$name]);
        return $this;
    }

    /**
     * Get all registered validator names
     *
     * @return array<string> Array of validator names
     */
    public function getNames(): array
    {
        return array_keys($this->validators);
    }

    /**
     * Get all registered validators
     *
     * @return array<string, ValidatorInterface>
     */
    public function all(): array
    {
        return $this->validators;
    }

    /**
     * Clear all registered validators
     *
     * @return self Fluent interface
     */
    public function clear(): self
    {
        $this->validators = [];
        return $this;
    }

    /**
     * Get the count of registered validators
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->validators);
    }

    /**
     * Reset the singleton instance (mainly for testing)
     *
     * @return void
     */
    public static function reset(): void
    {
        if (self::$instance !== null) {
            self::$instance->clear();
            self::$instance = null;
        }
    }
}
