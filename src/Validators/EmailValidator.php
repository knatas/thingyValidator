<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\AbstractValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * RFC 5322 compliant email address validator
 *
 * Validates email addresses according to RFC 5322 standards using PHP's
 * built-in FILTER_VALIDATE_EMAIL filter with additional checks for
 * common issues.
 *
 * Features:
 * - RFC 5322 compliant validation
 * - Domain validation (optional DNS check via context)
 * - Disposable email detection (optional via context)
 * - Length validation
 *
 * Usage:
 * ```php
 * $validator = new EmailValidator();
 * $result = $validator->validate('user@example.com');
 *
 * // With DNS check
 * $context = new ValidationContext(['check_dns' => true]);
 * $result = $validator->validate('user@example.com', $context);
 * ```
 *
 * @package ThingyValidator\Validators
 */
class EmailValidator extends AbstractValidator
{
    /**
     * Maximum length for email addresses (RFC 5321)
     */
    private const MAX_EMAIL_LENGTH = 320;

    /**
     * Maximum length for local part (before @)
     */
    private const MAX_LOCAL_LENGTH = 64;

    /**
     * Maximum length for domain part (after @)
     */
    private const MAX_DOMAIN_LENGTH = 255;

    protected string $errorMessage = 'Invalid email address';
    protected string $successMessage = 'Valid email address';

    /**
     * Perform email validation
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
    {
        // Type check
        if (!is_string($value)) {
            return $this->failure('Email must be a string', ['type' => gettype($value)]);
        }

        // Trim whitespace
        $email = trim($value);

        // Empty check
        if ($email === '') {
            return $this->failure('Email cannot be empty');
        }

        // Length check
        if (mb_strlen($email) > self::MAX_EMAIL_LENGTH) {
            return $this->failure(
                $this->formatMessage('Email too long (max {max} characters)', ['max' => self::MAX_EMAIL_LENGTH]),
                ['length' => mb_strlen($email), 'max' => self::MAX_EMAIL_LENGTH]
            );
        }

        // Basic format validation using PHP filter
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->failure('Invalid email format', ['email' => $email]);
        }

        // Split into local and domain parts
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $this->failure('Email must contain exactly one @ symbol');
        }

        [$local, $domain] = $parts;

        // Validate local part length
        if (mb_strlen($local) > self::MAX_LOCAL_LENGTH) {
            return $this->failure(
                $this->formatMessage('Local part too long (max {max} characters)', ['max' => self::MAX_LOCAL_LENGTH]),
                ['local_length' => mb_strlen($local), 'max' => self::MAX_LOCAL_LENGTH]
            );
        }

        // Validate domain part length
        if (mb_strlen($domain) > self::MAX_DOMAIN_LENGTH) {
            return $this->failure(
                $this->formatMessage('Domain too long (max {max} characters)', ['max' => self::MAX_DOMAIN_LENGTH]),
                ['domain_length' => mb_strlen($domain), 'max' => self::MAX_DOMAIN_LENGTH]
            );
        }

        // Optional DNS validation
        if ($context?->get('check_dns', false)) {
            if (!$this->validateDns($domain)) {
                return $this->failure(
                    'Domain has no valid MX or A records',
                    ['domain' => $domain, 'dns_check' => 'failed']
                );
            }
        }

        // Optional disposable email check
        if ($context?->get('check_disposable', false)) {
            $disposableDomains = $context->get('disposable_domains', []);
            if ($this->isDisposable($domain, $disposableDomains)) {
                return $this->warning(
                    'Email appears to be from a disposable email provider',
                    ['domain' => $domain, 'disposable' => true]
                );
            }
        }

        return $this->success();
    }

    /**
     * Validate domain has MX or A records
     *
     * @param string $domain The domain to check
     * @return bool True if domain has valid DNS records
     */
    private function validateDns(string $domain): bool
    {
        // Check for MX records first (preferred for email)
        if (checkdnsrr($domain, 'MX')) {
            return true;
        }

        // Fall back to A record check
        if (checkdnsrr($domain, 'A')) {
            return true;
        }

        // Check AAAA record for IPv6
        if (checkdnsrr($domain, 'AAAA')) {
            return true;
        }

        return false;
    }

    /**
     * Check if domain is a known disposable email provider
     *
     * @param string $domain The domain to check
     * @param array<string> $disposableDomains List of disposable domains
     * @return bool True if domain is disposable
     */
    private function isDisposable(string $domain, array $disposableDomains): bool
    {
        $domain = strtolower($domain);

        foreach ($disposableDomains as $disposable) {
            if (strtolower($disposable) === $domain) {
                return true;
            }
        }

        return false;
    }
}
