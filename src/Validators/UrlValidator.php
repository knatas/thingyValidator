<?php

declare(strict_types=1);

namespace ThingyValidator\Validators;

use ThingyValidator\AbstractValidator;
use ThingyValidator\ValidationContext;
use ThingyValidator\ValidationResult;

/**
 * URL format validator with protocol checking
 *
 * Validates URLs according to RFC 3986 standards with configurable
 * protocol requirements and additional security checks.
 *
 * Features:
 * - RFC 3986 compliant validation
 * - Protocol whitelist/blacklist
 * - DNS validation (optional)
 * - Path and query string validation
 * - IPv4/IPv6 support
 *
 * Usage:
 * ```php
 * $validator = new UrlValidator();
 * $result = $validator->validate('https://example.com');
 *
 * // Require specific protocols
 * $context = new ValidationContext(['allowed_protocols' => ['https']]);
 * $result = $validator->validate('https://example.com', $context);
 * ```
 *
 * @package ThingyValidator\Validators
 */
class UrlValidator extends AbstractValidator
{
    /**
     * Maximum URL length
     */
    private const MAX_URL_LENGTH = 2048;

    /**
     * Default allowed protocols
     */
    private const DEFAULT_ALLOWED_PROTOCOLS = ['http', 'https', 'ftp', 'ftps'];

    protected string $errorMessage = 'Invalid URL';
    protected string $successMessage = 'Valid URL';

    /**
     * Perform URL validation
     *
     * @param mixed $value The value to validate
     * @param ValidationContext|null $context Optional validation context
     * @return ValidationResult The validation result
     */
    protected function doValidate(mixed $value, ?ValidationContext $context): ValidationResult
    {
        // Type check
        if (!is_string($value)) {
            return $this->failure('URL must be a string', ['type' => gettype($value)]);
        }

        // Trim whitespace
        $url = trim($value);

        // Empty check
        if ($url === '') {
            return $this->failure('URL cannot be empty');
        }

        // Length check
        if (mb_strlen($url) > self::MAX_URL_LENGTH) {
            return $this->failure(
                $this->formatMessage('URL too long (max {max} characters)', ['max' => self::MAX_URL_LENGTH]),
                ['length' => mb_strlen($url), 'max' => self::MAX_URL_LENGTH]
            );
        }

        // Basic format validation using PHP filter
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return $this->failure('Invalid URL format', ['url' => $url]);
        }

        // Parse URL components
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme']) || !isset($parts['host'])) {
            return $this->failure('URL must contain scheme and host', ['url' => $url]);
        }

        // Validate protocol/scheme
        $scheme = strtolower($parts['scheme']);
        $allowedProtocols = $context !== null
            ? $context->get('allowed_protocols', self::DEFAULT_ALLOWED_PROTOCOLS)
            : self::DEFAULT_ALLOWED_PROTOCOLS;

        if (!in_array($scheme, $allowedProtocols, true)) {
            return $this->failure(
                $this->formatMessage('Protocol "{scheme}" is not allowed', ['scheme' => $scheme]),
                ['scheme' => $scheme, 'allowed' => $allowedProtocols]
            );
        }

        // Validate host
        $host = $parts['host'];
        if (!$this->validateHost($host)) {
            return $this->failure('Invalid host format', ['host' => $host]);
        }

        // Optional DNS validation
        if ($context?->get('check_dns', false)) {
            if (!$this->validateDns($host)) {
                return $this->failure(
                    'Host has no valid DNS records',
                    ['host' => $host, 'dns_check' => 'failed']
                );
            }
        }

        // Optional HTTPS requirement
        if ($context?->get('require_https', false) && $scheme !== 'https') {
            return $this->failure(
                'HTTPS protocol is required',
                ['scheme' => $scheme]
            );
        }

        // Optional path validation
        if ($context?->get('require_path', false) && empty($parts['path'])) {
            return $this->failure('URL must contain a path');
        }

        // Warn about suspicious patterns
        if ($this->hasSuspiciousPattern($url)) {
            return $this->warning(
                'URL contains potentially suspicious patterns',
                ['url' => $url, 'suspicious' => true]
            );
        }

        return $this->success();
    }

    /**
     * Validate host format (domain or IP address)
     *
     * @param string $host The host to validate
     * @return bool True if valid host
     */
    private function validateHost(string $host): bool
    {
        // Check if it's a valid IPv4 address
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return true;
        }

        // Check if it's a valid IPv6 address (remove brackets if present)
        $ipv6Host = trim($host, '[]');
        if (filter_var($ipv6Host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return true;
        }

        // Check if it's a valid domain name
        if (filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false) {
            return true;
        }

        // Additional domain validation
        if (preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i', $host)) {
            return true;
        }

        return false;
    }

    /**
     * Validate host has DNS records
     *
     * @param string $host The host to check
     * @return bool True if host has valid DNS records
     */
    private function validateDns(string $host): bool
    {
        // Skip DNS check for IP addresses
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        // Check for any DNS record
        if (checkdnsrr($host, 'A')) {
            return true;
        }

        if (checkdnsrr($host, 'AAAA')) {
            return true;
        }

        if (checkdnsrr($host, 'CNAME')) {
            return true;
        }

        return false;
    }

    /**
     * Check for suspicious patterns in URL
     *
     * @param string $url The URL to check
     * @return bool True if suspicious patterns detected
     */
    private function hasSuspiciousPattern(string $url): bool
    {
        // Multiple @ symbols (phishing attempt)
        if (substr_count($url, '@') > 1) {
            return true;
        }

        // Excessive dots (obfuscation)
        if (substr_count($url, '..') > 0) {
            return true;
        }

        // URL encoding obfuscation
        if (preg_match('/%[0-9a-f]{2}/i', $url)) {
            $decoded = urldecode($url);
            if ($decoded !== $url && preg_match('/%[0-9a-f]{2}/i', $decoded)) {
                return true;
            }
        }

        return false;
    }
}
