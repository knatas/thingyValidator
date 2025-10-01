<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\ParameterizedValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * Validates Universally Unique Identifiers (UUID)
 *
 * Validates UUID format according to RFC 4122 standard.
 * Supports versions 1, 3, 4, and 5 with configurable version checking.
 *
 * UUID format: xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx
 * - M = version (1, 3, 4, or 5)
 * - N = variant (8, 9, A, or B for RFC 4122)
 *
 * Usage:
 * ```php
 * // Validate any UUID version
 * $validator = new UuidValidator();
 * $result = $validator->validate('550e8400-e29b-41d4-a716-446655440000'); // Valid v4
 *
 * // Validate specific version
 * $validator = new UuidValidator(['version' => 4]);
 * $result = $validator->validate('550e8400-e29b-41d4-a716-446655440000'); // Valid v4
 * $result = $validator->validate('6ba7b810-9dad-11d1-80b4-00c04fd430c8'); // Invalid (v1)
 * ```
 *
 * @package ThingyValidator\Validators
 */
class UuidValidator extends ParameterizedValidator
{
    /**
     * Valid UUID versions
     */
    private const VALID_VERSIONS = [1, 3, 4, 5];

    /**
     * Create a new UUID validator
     *
     * @param array<string, mixed> $parameters Optional parameters:
     *   - 'version' (int): Specific UUID version to validate (1, 3, 4, or 5)
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct('uuid', $parameters);
        $this->errorMessage = 'Invalid UUID format';
        $this->successMessage = 'Valid UUID';
    }

    /**
     * Perform UUID validation
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
    {
        // Must be a string
        if (!is_string($value)) {
            return $this->failure(
                sprintf('UUID must be a string, %s given', gettype($value)),
                ['type' => gettype($value)]
            );
        }

        // Trim and convert to lowercase
        $uuid = strtolower(trim($value));

        // Cannot be empty
        if ($uuid === '') {
            return $this->failure(
                'UUID cannot be empty',
                ['value' => $value]
            );
        }

        // Basic format check: 8-4-4-4-12 hexadecimal pattern
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';
        if (!preg_match($pattern, $uuid)) {
            return $this->failure(
                sprintf('UUID "%s" does not match the standard format', $value),
                ['value' => $value, 'expected_format' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx']
            );
        }

        // Extract version (13th character, after 8+1+4+1)
        $versionChar = $uuid[14]; // Position 14 (0-indexed)
        $version = (int) $versionChar;

        // Validate version is supported
        if (!in_array($version, self::VALID_VERSIONS, true)) {
            return $this->failure(
                sprintf('UUID "%s" has invalid version %d (must be 1, 3, 4, or 5)', $value, $version),
                ['value' => $value, 'version' => $version, 'constraint' => 'version']
            );
        }

        // Extract variant (19th character, after 8+1+4+1+4+1)
        $variantChar = $uuid[19]; // Position 19 (0-indexed)

        // Validate variant is RFC 4122 compliant (8, 9, a, or b)
        if (!in_array($variantChar, ['8', '9', 'a', 'b'], true)) {
            return $this->failure(
                sprintf('UUID "%s" has invalid variant "%s" (must be 8, 9, A, or B)', $value, $variantChar),
                ['value' => $value, 'variant' => $variantChar, 'constraint' => 'variant']
            );
        }

        // Check if specific version is required
        $requiredVersion = $this->getParameterFromContext('version', $context);
        if ($requiredVersion !== null) {
            if (!in_array($requiredVersion, self::VALID_VERSIONS, true)) {
                return $this->failure(
                    sprintf('Invalid version parameter %d (must be 1, 3, 4, or 5)', $requiredVersion),
                    ['required_version' => $requiredVersion]
                );
            }

            if ($version !== $requiredVersion) {
                return $this->failure(
                    sprintf(
                        'UUID "%s" is version %d, but version %d is required',
                        $value,
                        $version,
                        $requiredVersion
                    ),
                    [
                        'value' => $value,
                        'actual_version' => $version,
                        'required_version' => $requiredVersion,
                        'constraint' => 'version_mismatch'
                    ]
                );
            }
        }

        // Success
        $message = sprintf('UUID "%s" is valid (version %d)', $value, $version);

        return $this->success($message, [
            'value' => $value,
            'normalized' => $uuid,
            'version' => $version,
            'variant' => $variantChar,
        ]);
    }
}
