# thingyValidator

A simple, dependency-free validation library for PHP 8.4+.
Perfect for projects that need quick and reliable input checks without pulling in heavy frameworks.

🚀 **Hacktoberfest-friendly**: this project is open to contributions! Add new validators, improve tests, or enhance documentation.

---

## ✨ Features

- **Minimal & Fast** - Pure PHP 8.4, zero external dependencies
- **Rich Built-in Validators** - Email, phone, URL, IBAN, UUID, numeric types, and more
- **Extensible Architecture** - Easy to add custom validators
- **Flexible API** - Simple boolean checks or detailed ValidationResult objects
- **Type-Safe** - Strict typing with PHP 8.4 features (readonly, enums)
- **Well Tested** - 230+ tests with comprehensive coverage
- **Context Support** - Pass additional validation parameters dynamically

---

## 📋 Requirements

- PHP 8.4 or higher
- Composer (for installation)
- OR Docker & Docker Compose (recommended for development)

---

## 📦 Installation

### Via Composer
```bash
composer require knatas/thingyvalidator
```

### Development with Docker
```bash
git clone https://github.com/knatas/thingyValidator.git
cd thingyValidator
docker-compose up -d
docker-compose exec thingyvalidator composer install
```

---

## 🛠 Quick Start

### Basic Usage - Boolean Validation

```php
<?php

require 'vendor/autoload.php';

use ThingyValidator\Validator;

$validator = new Validator();

// Email validation
$validator->isEmail('user@example.com');       // true
$validator->isEmail('invalid');                // false

// Phone validation
$validator->isPhone('+37061234567');           // true
$validator->isPhone('abc');                    // false

// URL validation
$validator->isUrl('https://example.com');      // true
$validator->isUrl('not-a-url');                // false

// String validation
$validator->isAlpha('hello');                  // true
$validator->isAlphanumeric('hello123');        // true
$validator->isLength('hello', 3, 10);          // true (between 3-10 chars)

// Numeric validation
$validator->isNumber(42);                      // true
$validator->isNumber('abc');                   // false

// Format validation
$validator->isIban('LT601010012345678901');    // true
$validator->isUuid('550e8400-e29b-41d4-a716-446655440000'); // true
```

### Advanced Usage - Detailed Results

```php
use ThingyValidator\Validator;
use ThingyValidator\ValidationContext;

$validator = new Validator();

// Get detailed validation result
$result = $validator->validate('email', 'user@example.com');

if ($result->isValid) {
    echo "Valid! " . $result->message;
} else {
    echo "Invalid! " . $result->message;
    print_r($result->errors);
}

// Use context for parameterized validation
$context = new ValidationContext(['min' => 10, 'max' => 100]);
$result = $validator->validate('integer', 50, $context);

// Access result details
echo $result->isValid;    // bool
echo $result->message;    // string|null
print_r($result->errors); // array
echo $result->type;       // ValidationResultType enum
```

---

## ✅ Available Validators

### String Validators
- **`isEmail(string $email): bool`** - RFC 5322 compliant email validation
- **`isUrl(string $url): bool`** - URL format with protocol checking
- **`isAlpha(string $string): bool`** - Alphabetic characters only
- **`isAlphanumeric(string $string): bool`** - Alphanumeric characters only
- **`isLength(string $string, int $min, int $max): bool`** - String length within bounds

### Numeric Validators
- **`isNumber(mixed $value): bool`** - Numeric value (int/float/numeric string)
- Integer validation with optional range (via direct validator access)
- Float validation with precision control (via direct validator access)

### Format Validators
- **`isPhone(string $phone): bool`** - International phone numbers (E.164 and common formats)
- **`isIban(string $iban): bool`** - International Bank Account Number with MOD-97 checksum
- **`isUuid(string $uuid): bool`** - UUID format (versions 1, 3, 4, 5)

---

## 📚 Detailed Examples

### Working with ValidationContext

```php
use ThingyValidator\Validator;
use ThingyValidator\ValidationContext;

$validator = new Validator();

// Length validation with context
$context = new ValidationContext(['min' => 5, 'max' => 20]);
$result = $validator->validate('length', 'hello', $context);

// Integer with range
$context = new ValidationContext(['min' => 1, 'max' => 100, 'strict' => true]);
$result = $validator->validate('integer', 42, $context);

// Float with precision
$context = new ValidationContext(['precision' => 2, 'min' => 0.0, 'max' => 100.0]);
$result = $validator->validate('float', 99.99, $context);

// UUID with specific version
$context = new ValidationContext(['version' => 4]);
$result = $validator->validate('uuid', '550e8400-e29b-41d4-a716-446655440000', $context);

// URL with custom protocols
$context = new ValidationContext(['allowed_protocols' => ['https']]);
$result = $validator->validate('url', 'https://example.com', $context);
```

### Creating Custom Validators

```php
use ThingyValidator\Validator;
use ThingyValidator\ValidatorInterface;
use ThingyValidator\ValidationResult;
use ThingyValidator\ValidationContext;

// Create a custom validator
class PostalCodeValidator implements ValidatorInterface
{
    public function validate(mixed $value, ?ValidationContext $context = null): ValidationResult
    {
        if (!is_string($value)) {
            return ValidationResult::failure('Postal code must be a string');
        }

        // US postal code pattern
        if (preg_match('/^\d{5}(-\d{4})?$/', $value)) {
            return ValidationResult::success('Valid postal code');
        }

        return ValidationResult::failure('Invalid postal code format');
    }

    public function getName(): string
    {
        return 'postal_code';
    }
}

// Register and use custom validator
$validator = new Validator();
$validator->registerValidator(new PostalCodeValidator());

$result = $validator->validate('postal_code', '12345');
echo $result->isValid ? 'Valid!' : 'Invalid!';
```

### Using SimpleValidator for Quick Validators

```php
use ThingyValidator\Validator;
use ThingyValidator\SimpleValidator;

$validator = new Validator();

// Create validator from callable
$isEven = new SimpleValidator(
    'even',
    fn($value) => is_int($value) && $value % 2 === 0,
    'Value must be an even number'
);

$validator->registerValidator($isEven);

$result = $validator->validate('even', 42);  // Valid
$result = $validator->validate('even', 43);  // Invalid
```

### Validation Result Types

```php
use ThingyValidator\ValidationResultType;

$result = $validator->validate('email', 'test@example.com');

match ($result->type) {
    ValidationResultType::Success => echo "Validation passed!",
    ValidationResultType::Failure => echo "Validation failed!",
    ValidationResultType::Warning => echo "Validation passed with warnings",
};
```

---

## 🎯 Use Cases

### Form Validation
```php
$validator = new Validator();
$errors = [];

if (!$validator->isEmail($_POST['email'])) {
    $errors['email'] = 'Invalid email address';
}

if (!$validator->isPhone($_POST['phone'])) {
    $errors['phone'] = 'Invalid phone number';
}

if (!$validator->isLength($_POST['message'], 10, 500)) {
    $errors['message'] = 'Message must be between 10 and 500 characters';
}

if (empty($errors)) {
    // Process form
}
```

### API Input Validation
```php
$validator = new Validator();

$data = json_decode(file_get_contents('php://input'), true);

$result = $validator->validate('email', $data['email'] ?? '');
if (!$result->isValid) {
    http_response_code(400);
    echo json_encode(['error' => $result->message]);
    exit;
}
```

### Configuration Validation
```php
$validator = new Validator();
$context = new ValidationContext(['min' => 1, 'max' => 65535]);

if (!$validator->validate('integer', $config['port'], $context)->isValid) {
    throw new ConfigurationException('Invalid port number');
}
```

---

## 🧑‍💻 Contributing

We welcome contributions, especially during **Hacktoberfest** 🎉

### How to contribute:
1. **Fork** this repo
2. **Create a new branch**: `git checkout -b feature/add-new-validator`
3. **Add your validator** in `src/Validators/`
4. **Write comprehensive tests** in `tests/Validators/`
5. **Update documentation** in README and PHPDoc
6. **Commit & push** your changes
7. **Open a Pull Request**

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

### Ideas for contributions
- New validators (credit card, VAT numbers, postal codes, MAC addresses)
- Performance optimizations
- Additional test coverage
- Documentation improvements
- Examples and demos

---

## 📂 Project Structure

```
/src
  ├── Validator.php                 # Main facade class
  ├── ValidatorInterface.php        # Validator contract
  ├── ValidationResult.php          # Result value object
  ├── ValidationResultType.php      # Result type enum
  ├── ValidationContext.php         # Context object
  ├── ValidatorRegistry.php         # Registry pattern
  ├── AbstractValidator.php         # Base validator
  ├── SimpleValidator.php           # Simple callable validators
  ├── ParameterizedValidator.php    # Validators with parameters
  ├── ValidatorFactory.php          # Factory pattern
  └── /Validators                   # Built-in validators
      ├── EmailValidator.php
      ├── UrlValidator.php
      ├── PhoneValidator.php
      ├── AlphaValidator.php
      ├── AlphanumericValidator.php
      ├── LengthValidator.php
      ├── NumberValidator.php
      ├── IntegerValidator.php
      ├── FloatValidator.php
      ├── IbanValidator.php
      └── UuidValidator.php
/tests
  ├── ValidatorTestCase.php         # Base test class
  ├── /Core                         # Core component tests
  └── /Validators                   # Validator tests
/examples                           # Usage examples
```

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test suite
vendor/bin/phpunit tests/Validators/EmailValidatorTest.php

# With Docker
docker-compose exec thingyvalidator composer test
```

---

## ⚖️ License

MIT License - open source software distributed without any warranties or guarantees.
Use at your own risk and responsibility.

See [LICENSE](LICENSE) file for complete license terms.

---

## 🌍 Hacktoberfest

This repository has the `hacktoberfest` topic enabled.
Meaningful pull requests opened in October will count toward Hacktoberfest.

Spammy or low-quality PRs will be marked as `invalid` or `spam`.
Please follow the [contribution guide](CONTRIBUTING.md) 🙏

---

## 📖 Further Reading

- [CLAUDE.md](CLAUDE.md) - Project architecture and development guidelines
- [CONTRIBUTING.md](CONTRIBUTING.md) - Contribution guidelines
- [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) - Community standards

---

**Made with ❤️ for the PHP community**
