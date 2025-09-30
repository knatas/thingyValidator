# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

thingyValidator is a simple, dependency-free validation library for PHP 8.4+. The project is designed to be minimal and fast with pure PHP implementation and no external dependencies.

## Project Structure

Based on the README, the expected structure is:
```
/src
  Validator.php       # Main validator class with all validation methods
/tests
  ValidatorTest.php   # PHPUnit tests for all validators
composer.json         # Composer package configuration
```

**Note**: The actual source files are not yet present in the repository - this appears to be a work-in-progress project ready for implementation.

## Development Commands

### Local Development (requires PHP 8.4+)
```bash
composer install
vendor/bin/phpunit tests/
composer validate
```

### Docker Development (recommended)
```bash
# Build and start development environment
docker-compose up -d

# Run commands in container
docker-compose exec thingyvalidator composer install
docker-compose exec thingyvalidator vendor/bin/phpunit tests/
docker-compose exec thingyvalidator php -f examples/demo.php

# Interactive PHP shell
docker-compose exec thingyvalidator php -a

# Start web server for examples (optional)
docker-compose --profile web up -d
# Access at http://localhost:8080

# Stop all services
docker-compose down
```

### Code Quality
```bash
composer validate  # Validate composer.json
```

## Architecture Guidelines

### Core Architecture (Extensible Design)

The library uses a plugin-based architecture with these key components:

- **ValidatorInterface**: Contract for all validators (built-in and custom)
- **ValidationResult**: Value object containing validation outcome and detailed error info  
- **ValidatorRegistry**: Registry for managing validator instances
- **Validator**: Main facade class providing convenience methods
- **ValidationContext**: Optional context object for complex validation scenarios

### Key Design Patterns

- **Strategy Pattern**: Each validator is an independent strategy
- **Registry Pattern**: Central registry for validator discovery and management
- **Factory Pattern**: For creating validators with dependencies
- **Facade Pattern**: Simple API wrapper for complex internal architecture

### PHP 8.4 Features Leveraged

- **Readonly Properties**: Immutable ValidationResult objects
- **Enums**: ValidationResultType, ErrorSeverity
- **Union Types**: Flexible parameter types for validators
- **Attributes**: Metadata for validator configuration
- **First-class Callables**: For custom validation functions

### Extensibility Points

1. **Custom Validators**: Implement ValidatorInterface and register
2. **Validation Contexts**: Provide additional data (DB connections, config)
3. **Result Formatters**: Custom error message formatting
4. **Middleware**: Pre/post validation hooks
5. **Composite Validators**: Combine multiple validators into complex rules

### Core Interfaces

```php
interface ValidatorInterface
{
    public function validate(mixed $value, ?ValidationContext $context = null): ValidationResult;
    public function getName(): string;
}

readonly class ValidationResult
{
    public function __construct(
        public bool $isValid,
        public ?string $message = null,
        public array $errors = [],
        public ?ValidationResultType $type = null
    ) {}
}
```

## Available Validators (to implement)

- `isEmail($string)`
- `isPhone($string)` 
- `isUrl($string)`
- `isNumber($value)`
- `isAlpha($string)`
- `isAlphanumeric($string)`
- `isLength($string, $min, $max)`

## Contributing Workflow

1. Add new validator methods to `src/Validator.php`
2. Write corresponding unit tests in `tests/ValidatorTest.php`
3. Follow the `is{Type}()` naming convention
4. Ensure all methods return boolean values
5. Keep implementations simple and dependency-free

## License

This project is open source software distributed without any warranties or guarantees. 
Use at your own risk and responsibility.

See [LICENSE](LICENSE) file for complete license terms.