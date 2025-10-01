<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Factory for creating validator instances
 *
 * Provides convenient factory methods for creating validators without
 * having to instantiate classes directly. Supports dependency injection
 * and custom validator creation.
 *
 * Usage:
 * ```php
 * $factory = new ValidatorFactory();
 *
 * // Create simple validator from callable
 * $isEven = $factory->createSimple(
 *     'even',
 *     fn($value) => is_int($value) && $value % 2 === 0
 * );
 *
 * // Create parameterized validator
 * $range = $factory->create(RangeValidator::class, ['min' => 0, 'max' => 100]);
 * ```
 *
 * @package ThingyValidator
 */
class ValidatorFactory
{
    /**
     * @var ValidatorRegistry The validator registry
     */
    private ValidatorRegistry $registry;

    /**
     * @var array<string, class-string<ValidatorInterface>> Registered validator class mappings
     */
    private array $validatorClasses = [];

    /**
     * Create a new validator factory
     *
     * @param ValidatorRegistry|null $registry Optional custom registry instance
     */
    public function __construct(?ValidatorRegistry $registry = null)
    {
        $this->registry = $registry ?? ValidatorRegistry::getInstance();
    }

    /**
     * Create a simple validator from a callable
     *
     * @param string $name The validator name
     * @param callable(mixed, ?ValidationContext): bool $validationCallable Validation callable
     * @param string|null $errorMessage Optional error message
     * @param string|null $successMessage Optional success message
     * @param bool $autoRegister Whether to automatically register the validator
     * @return SimpleValidator
     */
    public function createSimple(
        string $name,
        callable $validationCallable,
        ?string $errorMessage = null,
        ?string $successMessage = null,
        bool $autoRegister = false
    ): SimpleValidator {
        $validator = new SimpleValidator($name, $validationCallable, $errorMessage, $successMessage);

        if ($autoRegister) {
            $this->registry->register($validator);
        }

        return $validator;
    }

    /**
     * Create a validator instance by class name
     *
     * @template T of ValidatorInterface
     * @param class-string<T> $className The validator class name
     * @param array<string, mixed> $parameters Constructor parameters
     * @param bool $autoRegister Whether to automatically register the validator
     * @return T
     * @throws \InvalidArgumentException If class doesn't exist or doesn't implement ValidatorInterface
     */
    public function create(string $className, array $parameters = [], bool $autoRegister = false): ValidatorInterface
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Validator class "%s" does not exist', $className));
        }

        if (!is_subclass_of($className, ValidatorInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('Class "%s" must implement ValidatorInterface', $className)
            );
        }

        // Handle different constructor signatures
        $validator = $this->instantiateValidator($className, $parameters);

        if ($autoRegister) {
            $this->registry->register($validator);
        }

        return $validator;
    }

    /**
     * Register a validator class with a short name for easy creation
     *
     * @param string $name Short name for the validator
     * @param class-string<ValidatorInterface> $className The validator class name
     * @return self Fluent interface
     */
    public function registerClass(string $name, string $className): self
    {
        $this->validatorClasses[$name] = $className;
        return $this;
    }

    /**
     * Create a validator by registered short name
     *
     * @param string $name The registered short name
     * @param array<string, mixed> $parameters Constructor parameters
     * @param bool $autoRegister Whether to automatically register the validator
     * @return ValidatorInterface
     * @throws \InvalidArgumentException If name is not registered
     */
    public function createByName(string $name, array $parameters = [], bool $autoRegister = false): ValidatorInterface
    {
        if (!isset($this->validatorClasses[$name])) {
            throw new \InvalidArgumentException(sprintf('Validator "%s" is not registered in factory', $name));
        }

        return $this->create($this->validatorClasses[$name], $parameters, $autoRegister);
    }

    /**
     * Create and register a validator in one call
     *
     * @template T of ValidatorInterface
     * @param class-string<T> $className The validator class name
     * @param array<string, mixed> $parameters Constructor parameters
     * @param bool $overwrite Whether to overwrite existing validator
     * @return T
     */
    public function createAndRegister(string $className, array $parameters = [], bool $overwrite = false): ValidatorInterface
    {
        $validator = $this->create($className, $parameters);
        $this->registry->register($validator, $overwrite);
        return $validator;
    }

    /**
     * Create multiple validators at once
     *
     * @param array<string, array{class: class-string<ValidatorInterface>, params?: array<string, mixed>}> $definitions
     * @param bool $autoRegister Whether to automatically register all validators
     * @return array<string, ValidatorInterface>
     */
    public function createMany(array $definitions, bool $autoRegister = false): array
    {
        $validators = [];

        foreach ($definitions as $name => $definition) {
            $className = $definition['class'];
            $params = $definition['params'] ?? [];

            $validators[$name] = $this->create($className, $params, $autoRegister);
        }

        return $validators;
    }

    /**
     * Get the validator registry
     *
     * @return ValidatorRegistry
     */
    public function getRegistry(): ValidatorRegistry
    {
        return $this->registry;
    }

    /**
     * Instantiate a validator with appropriate constructor parameters
     *
     * @param class-string<ValidatorInterface> $className The validator class name
     * @param array<string, mixed> $parameters Constructor parameters
     * @return ValidatorInterface
     */
    private function instantiateValidator(string $className, array $parameters): ValidatorInterface
    {
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            // No constructor, just instantiate
            return new $className();
        }

        $constructorParams = $constructor->getParameters();

        if (empty($constructorParams)) {
            // Constructor has no parameters
            return new $className();
        }

        // Build constructor arguments based on parameter names
        $args = [];
        foreach ($constructorParams as $param) {
            $paramName = $param->getName();

            if (array_key_exists($paramName, $parameters)) {
                $args[] = $parameters[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } elseif ($param->allowsNull()) {
                $args[] = null;
            } else {
                // Try to pass entire parameters array if first param accepts array
                if ($param->getType()?->getName() === 'array' && $param->getPosition() === 0) {
                    return new $className($parameters);
                }

                throw new \InvalidArgumentException(
                    sprintf(
                        'Missing required constructor parameter "%s" for validator "%s"',
                        $paramName,
                        $className
                    )
                );
            }
        }

        return new $className(...$args);
    }
}
