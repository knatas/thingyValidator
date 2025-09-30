# thingyValidator

A simple, dependency-free validation library for PHP.  
Perfect for projects that need quick and reliable input checks without pulling in heavy frameworks.

🚀 **Hacktoberfest-friendly**: this project is open to contributions! Add new validators, improve tests, or enhance documentation.  

---

## ✨ Features
- Minimal & fast (pure PHP, no external deps)
- Common built-in validators (`isEmail`, `isPhone`, `isUrl`, etc.)
- Easy to extend with custom rules
- Tested with PHPUnit
- Clear and simple API

---

## 📦 Installation
```bash
composer require knatas/thingyvalidator
```

---

## 🛠 Usage
```php
<?php

require 'vendor/autoload.php';

use ThingyValidator\Validator;

$validator = new Validator();

$validator->isEmail("user@example.com");       // true
$validator->isPhone("+37061234567");           // true
$validator->isIban("LT601010012345678901");    // true
$validator->isLength("hello", 3, 10);          // true
```

---

## ✅ Available Validators
- `isEmail($string)`
- `isPhone($string)`
- `isUrl($string)`
- `isNumber($value)`
- `isAlpha($string)`
- `isAlphanumeric($string)`
- `isLength($string, $min, $max)`
- *(more coming soon!)*

---

## 🧑‍💻 Contributing

We welcome contributions, especially during **Hacktoberfest** 🎉  

### How to contribute:
1. **Fork** this repo  
2. **Create a new branch**:  
   ```bash
   git checkout -b feature/add-iban-validator
   ```
3. **Add your validator** in `src/Validator.php`  
4. **Write a unit test** in `tests/ValidatorTest.php`  
5. **Commit & push** your changes  
6. **Open a Pull Request**  

---

### Ideas for contributions
- Add new validators (IBAN, VAT, UUID, postal codes, credit cards, etc.)
- Write unit tests for existing validators
- Improve documentation & examples
- Add localization support for error messages
- Build a demo page showcasing the library

---

## 📂 Project Structure
```
/src
  Validator.php
/tests
  ValidatorTest.php
README.md
CONTRIBUTING.md
composer.json
```

---

## ⚖️ License
MIT License. See [LICENSE](LICENSE) for details.

---

## 🌍 Hacktoberfest Note
This repository has the `hacktoberfest` topic enabled.  
Meaningful pull requests opened here in October will count toward Hacktoberfest.  

Spammy or low-quality PRs will be marked as `invalid` or `spam`.  
Please follow the [contribution guide](CONTRIBUTING.md) 🙏
