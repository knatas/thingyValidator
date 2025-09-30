<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Optional context object for complex validation scenarios
 *
 * Provides additional data and configuration for validators without
 * requiring external dependencies. Can store database connections,
 * configuration options, or any other context-specific data.
 *
 * @package ThingyValidator
 */
class ValidationContext
{
    /**
     * @var array<string, mixed> Context data storage
     */
    private array $data = [];

    /**
     * Create a new validation context
     *
     * @param array<string, mixed> $data Initial context data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Set a context value
     *
     * @param string $key The context key
     * @param mixed $value The context value
     * @return self Fluent interface
     */
    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get a context value
     *
     * @param string $key The context key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The context value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if a context key exists
     *
     * @param string $key The context key
     * @return bool True if the key exists
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove a context value
     *
     * @param string $key The context key to remove
     * @return self Fluent interface
     */
    public function remove(string $key): self
    {
        unset($this->data[$key]);
        return $this;
    }

    /**
     * Get all context data
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Clear all context data
     *
     * @return self Fluent interface
     */
    public function clear(): self
    {
        $this->data = [];
        return $this;
    }
}
