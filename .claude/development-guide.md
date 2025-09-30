# Development Guide

## Quick Start

1. **Setup**: `composer install` 
2. **Test**: `vendor/bin/phpunit`
3. **Validate**: `composer validate`

## Adding a New Validator

### 1. Create Validator Class
```php
// src/Validators/MyValidator.php
class MyValidator implements ValidatorInterface
{
    public function validate(mixed $value, ?ValidationContext $context = null): ValidationResult
    {
        // Implementation
        return new ValidationResult($isValid, $message);
    }
    
    public function getName(): string
    {
        return 'my_validator';
    }
}
```

### 2. Register in Validator Class
```php
// In src/Validator.php registerBuiltInValidators() method
$this->registry->register('my_validator', new MyValidator());
```

### 3. Add Convenience Method
```php
// In src/Validator.php
public function isMy(mixed $value): bool
{
    return $this->validate('my_validator', $value)->isValid;
}
```

### 4. Write Tests
```php
// tests/Validators/MyValidatorTest.php
class MyValidatorTest extends ValidatorTestCase
{
    public function testValidInput(): void
    {
        $validator = new MyValidator();
        $result = $validator->validate('valid_input');
        $this->assertTrue($result->isValid);
    }
}
```

## Architecture Overview

```
ValidatorInterface ← AbstractValidator ← SpecificValidators
        ↑
ValidatorRegistry → Validator (Facade)
        ↑
ValidationResult ← Validators
```

## Key Principles

- **Immutable Results**: ValidationResult is readonly
- **Context Optional**: Most validators work without context
- **Registry Pattern**: All validators registered in central registry
- **Facade Simplicity**: Main Validator class provides simple API
- **Strategy Pattern**: Each validator is independent strategy

## Testing Patterns

```php
// Test valid cases
public function validDataProvider(): array
{
    return [
        ['valid@email.com'],
        ['another@test.org'],
    ];
}

// Test invalid cases  
public function invalidDataProvider(): array
{
    return [
        ['invalid-email'],
        ['@missing-local.com'],
    ];
}
```

## Performance Guidelines

- Use early returns for obvious failures
- Avoid regex when simple string functions work
- Cache compiled regex patterns in static properties
- Prefer native PHP functions over custom implementations