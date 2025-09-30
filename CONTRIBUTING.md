# Contributing to thingyValidator

Thank you for your interest in contributing to thingyValidator! This document provides guidelines and information for contributors.

## 🎯 Hacktoberfest Participants

This project welcomes Hacktoberfest contributions! We're looking for meaningful contributions that add real value to the library.

### What We're Looking For:
- ✅ New validator implementations
- ✅ Comprehensive unit tests
- ✅ Documentation improvements
- ✅ Bug fixes and performance optimizations
- ✅ Examples and usage demonstrations

### What We Don't Want:
- ❌ Minor typo fixes in documentation
- ❌ Whitespace-only changes
- ❌ Duplicate implementations
- ❌ Low-effort contributions

## 🚀 Getting Started

### Prerequisites
- PHP 8.4 or higher
- Composer
- OR Docker & Docker Compose (recommended)

### Development Setup

#### Option 1: Docker (Recommended)
```bash
git clone https://github.com/knatas/thingyValidator.git
cd thingyValidator
docker-compose up -d
docker-compose exec thingyvalidator composer install
```

#### Option 2: Local PHP
```bash
git clone https://github.com/knatas/thingyValidator.git
cd thingyValidator
composer install
```

## 📝 How to Contribute

### 1. Choose Your Contribution

Check our [Issues](https://github.com/knatas/thingyValidator/issues) for:
- `good first issue` - Perfect for newcomers
- `hacktoberfest` - Specifically tagged for Hacktoberfest
- `help wanted` - We need community help

### 2. Fork and Branch
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/add-credit-card-validator`
3. Use descriptive branch names

### 3. Implementation Guidelines

#### Adding a New Validator
1. **Create the validator class** in `src/Validators/`
2. **Implement ValidatorInterface** with proper validation logic
3. **Register in ValidatorRegistry** 
4. **Add convenience method** to main `Validator` class
5. **Write comprehensive tests** in `tests/Validators/`

Example structure:
```php
// src/Validators/CreditCardValidator.php
class CreditCardValidator implements ValidatorInterface
{
    public function validate(mixed $value, ?ValidationContext $context = null): ValidationResult
    {
        // Implementation
    }
    
    public function getName(): string
    {
        return 'credit_card';
    }
}
```

#### Testing Requirements
- **100% test coverage** for new validators
- **Edge cases and boundary conditions**
- **Invalid input handling**
- **Performance considerations**

### 4. Code Quality Standards

#### PHP Standards
- Follow **PSR-12** coding standards
- Use **PHP 8.4 features** where appropriate
- **Zero external dependencies** (pure PHP only)
- **Type declarations** for all parameters and return values

#### Documentation
- **PHPDoc comments** for all public methods
- **Usage examples** for complex validators
- **Clear error messages** for validation failures

### 5. Testing Your Changes

```bash
# Run tests
docker-compose exec thingyvalidator composer test

# Check test coverage
docker-compose exec thingyvalidator composer test:coverage

# Validate composer.json
docker-compose exec thingyvalidator composer validate
```

### 6. Submit Your Pull Request

1. **Commit your changes** with clear, descriptive messages
2. **Push to your fork**: `git push origin feature/add-credit-card-validator`
3. **Open a Pull Request** with:
   - Clear title describing the change
   - Detailed description of what was added/fixed
   - Link to related issues
   - Test coverage information

## 🎨 Contribution Ideas

### High Priority Validators
- **Credit Card Numbers** (Luhn algorithm)
- **International VAT Numbers**
- **Postal Codes** (country-specific)
- **Social Security Numbers**
- **ISBN/ISSN Numbers**
- **MAC Addresses**
- **IP Addresses** (IPv4/IPv6)

### Testing & Quality
- **Performance benchmarks**
- **Stress testing with large datasets**
- **Memory usage optimization**
- **Error handling improvements**

### Documentation & Examples
- **Usage examples** for each validator
- **Integration guides** for popular frameworks
- **Performance comparison documentation**
- **Migration guides** from other validation libraries

## 🔄 Review Process

1. **Automated checks** must pass (CI/CD)
2. **Code review** by maintainers
3. **Test coverage** verification
4. **Performance impact** assessment
5. **Documentation** completeness check

## 📋 Pull Request Checklist

- [ ] Tests added/updated and passing
- [ ] Documentation updated
- [ ] Code follows project standards
- [ ] No breaking changes (or clearly documented)
- [ ] Commit messages are descriptive
- [ ] PR description is clear and complete

## 🆘 Getting Help

- **GitHub Issues**: For bugs and feature requests
- **GitHub Discussions**: For questions and general discussion
- **Code Review**: Don't hesitate to ask for feedback

## 📜 Code of Conduct

This project adheres to a [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## 🏆 Recognition

Contributors will be:
- Listed in project documentation
- Mentioned in release notes
- Added to CONTRIBUTORS.md file

Thank you for helping make thingyValidator better! 🙏