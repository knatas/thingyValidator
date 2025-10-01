<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Main facade class providing convenient validation methods
 *
 * This class serves as the primary entry point for the validation library,
 * offering both simple boolean validation methods (for backward compatibility)
 * and detailed validation through the registry system.
 *
 * Usage:
 * ```php
 * $validator = new Validator();
 *
 * // Simple boolean validation
 * $validator->isEmail('test@example.com'); // true
 *
 * // Detailed validation with result object
 * $result = $validator->validateEmail('test@example.com');
 * if ($result->isValid) {
 *     echo "Valid email!";
 * }
 * ```
 *
 * @package ThingyValidator
 */
class Validator
{
    /**
     * @var ValidatorRegistry The validator registry instance
     */
    private ValidatorRegistry $registry;

    /**
     * @var ValidationContext|null Optional validation context
     */
    private ?ValidationContext $context = null;

    /**
     * Create a new Validator instance
     *
     * @param ValidatorRegistry|null $registry Optional custom registry instance
     * @param bool $autoRegister Auto-register built-in validators (default: true)
     */
    public function __construct(?ValidatorRegistry $registry = null, bool $autoRegister = true)
    {
        $this->registry = $registry ?? ValidatorRegistry::getInstance();

        if ($autoRegister) {
            $this->registerBuiltInValidators();
        }
    }

    /**
     * Register all built-in validators
     *
     * This method auto-registers the standard validators so convenience
     * methods work out of the box. Can be disabled via constructor.
     * Only registers validators that exist (class_exists check).
     *
     * @return void
     */
    private function registerBuiltInValidators(): void
    {
        // Map of validator names to class names
        // Only registers if class exists (supports partial implementations)
        $validatorsToRegister = [
            'email' => \ThingyValidator\Validators\EmailValidator::class,
            'url' => \ThingyValidator\Validators\UrlValidator::class,
            'phone' => \ThingyValidator\Validators\PhoneValidator::class,
            'alpha' => \ThingyValidator\Validators\AlphaValidator::class,
            'alphanumeric' => \ThingyValidator\Validators\AlphanumericValidator::class,
            'length' => \ThingyValidator\Validators\LengthValidator::class,
            'number' => \ThingyValidator\Validators\NumberValidator::class,
            'integer' => \ThingyValidator\Validators\IntegerValidator::class,
            'float' => \ThingyValidator\Validators\FloatValidator::class,
            'iban' => \ThingyValidator\Validators\IbanValidator::class,
            'uuid' => \ThingyValidator\Validators\UuidValidator::class,
        ];

        foreach ($validatorsToRegister as $name => $class) {
            // Only register if:
            // 1. Validator doesn't already exist in registry
            // 2. Class file exists (supports gradual validator implementation)
            if (!$this->registry->has($name) && class_exists($class)) {
                $validator = new $class();
                $this->registry->register($validator);
            }
        }
    }

    /**
     * Set the validation context
     *
     * @param ValidationContext $context The context to use
     * @return self Fluent interface
     */
    public function withContext(ValidationContext $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get the current validation context
     *
     * @return ValidationContext|null
     */
    public function getContext(): ?ValidationContext
    {
        return $this->context;
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
     * Validate a value using a registered validator
     *
     * @param string $validatorName The name of the validator to use
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional context (overrides instance context)
     * @return ValidationResult
     * @throws \RuntimeException If validator is not registered
     */
    public function validate(string $validatorName, mixed $value, ?ValidationContext $context = null): ValidationResult
    {
        $validator = $this->registry->get($validatorName);

        if ($validator === null) {
            throw new \RuntimeException(
                sprintf('Validator "%s" is not registered.', $validatorName)
            );
        }

        $contextToUse = $context ?? $this->context;
        return $validator->validate($value, $contextToUse);
    }

    /**
     * Register a custom validator
     *
     * @param ValidatorInterface $validator The validator to register
     * @param bool $overwrite Whether to overwrite existing validator
     * @return self Fluent interface
     */
    public function registerValidator(ValidatorInterface $validator, bool $overwrite = false): self
    {
        $this->registry->register($validator, $overwrite);
        return $this;
    }

    // ==========================================
    // Convenience Methods (Boolean Returns)
    // ==========================================
    // These methods provide simple boolean validation
    // for common use cases without requiring detailed results.

    /**
     * Validate an email address
     *
     * @param string $email The email to validate
     * @return bool True if valid email format
     */
    public function isEmail(string $email): bool
    {
        $validator = $this->registry->get('email');
        if ($validator === null) {
            // Fallback to simple validation if validator not registered
            return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
        }

        return $validator->validate($email, $this->context)->isValid;
    }

    /**
     * Validate a phone number
     *
     * @param string $phone The phone number to validate
     * @return bool True if valid phone format
     */
    public function isPhone(string $phone): bool
    {
        $validator = $this->registry->get('phone');
        if ($validator === null) {
            // Basic fallback: non-empty and contains digits
            return !empty($phone) && preg_match('/\d/', $phone) === 1;
        }

        return $validator->validate($phone, $this->context)->isValid;
    }

    /**
     * Validate a URL
     *
     * @param string $url The URL to validate
     * @return bool True if valid URL format
     */
    public function isUrl(string $url): bool
    {
        $validator = $this->registry->get('url');
        if ($validator === null) {
            // Fallback to simple validation
            return (bool) filter_var($url, FILTER_VALIDATE_URL);
        }

        return $validator->validate($url, $this->context)->isValid;
    }

    /**
     * Validate if value is numeric
     *
     * @param mixed $value The value to validate
     * @return bool True if numeric
     */
    public function isNumber(mixed $value): bool
    {
        $validator = $this->registry->get('number');
        if ($validator === null) {
            // Fallback to simple validation
            return is_numeric($value);
        }

        return $validator->validate($value, $this->context)->isValid;
    }

    /**
     * Validate if string contains only alphabetic characters
     *
     * @param string $string The string to validate
     * @return bool True if only alphabetic characters
     */
    public function isAlpha(string $string): bool
    {
        $validator = $this->registry->get('alpha');
        if ($validator === null) {
            // Fallback to simple validation
            return !empty($string) && ctype_alpha($string);
        }

        return $validator->validate($string, $this->context)->isValid;
    }

    /**
     * Validate if string contains only alphanumeric characters
     *
     * @param string $string The string to validate
     * @return bool True if only alphanumeric characters
     */
    public function isAlphanumeric(string $string): bool
    {
        $validator = $this->registry->get('alphanumeric');
        if ($validator === null) {
            // Fallback to simple validation
            return !empty($string) && ctype_alnum($string);
        }

        return $validator->validate($string, $this->context)->isValid;
    }

    /**
     * Validate string length is within bounds
     *
     * @param string $string The string to validate
     * @param int $min Minimum length (inclusive)
     * @param int $max Maximum length (inclusive)
     * @return bool True if length is within bounds
     */
    public function isLength(string $string, int $min, int $max): bool
    {
        $validator = $this->registry->get('length');
        if ($validator === null) {
            // Fallback to simple validation
            $length = mb_strlen($string);
            return $length >= $min && $length <= $max;
        }

        // Pass min/max through context
        $context = new ValidationContext(['min' => $min, 'max' => $max]);
        return $validator->validate($string, $context)->isValid;
    }

    /**
     * Validate IBAN (International Bank Account Number)
     *
     * @param string $iban The IBAN to validate
     * @return bool True if valid IBAN format
     */
    public function isIban(string $iban): bool
    {
        $validator = $this->registry->get('iban');
        if ($validator === null) {
            // No fallback for complex validation - return false
            return false;
        }

        return $validator->validate($iban, $this->context)->isValid;
    }

    /**
     * Validate UUID (Universally Unique Identifier)
     *
     * @param string $uuid The UUID to validate
     * @return bool True if valid UUID format
     */
    public function isUuid(string $uuid): bool
    {
        $validator = $this->registry->get('uuid');
        if ($validator === null) {
            // Basic UUID v4 pattern fallback
            $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
            return preg_match($pattern, $uuid) === 1;
        }

        return $validator->validate($uuid, $this->context)->isValid;
    }
}
