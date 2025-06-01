# PHP FormBuilder & Renderer

A lightweight, extensible system for building, rendering, and validating HTML forms in PHP.

## 🚀 Features

- Fluent form building interface (`FormBuilder`)
- Form rendering to HTML (`FormRenderer`)
- Validation rule support (e.g., Required, Email, MinLength, MaxLength, Numeric)
- Inline error display below each field
- Unit tested with PHPUnit

## 📦 Installation

Create a new PHP project:

```bash
composer init
composer install
```

Ensure your `composer.json` includes the repositories:

```json
"repositories": [{
        "type": "vcs",
        "url": "https://github.com/Tomazo16/form.git"
    }],
```

Install via composer:

```bash
composer require tomazo/form
```

## 🛠️ Usage

### 1. Create a form

```php
use App\Form\FormBuilder;
use App\Form\FormRenderer;
use App\Form\Validator\{RequiredRule, EmailRule, MinLengthRule};

$form = (new FormBuilder('/submit.php'))
    ->addField('name', 'Name', 'text', [
        new RequiredRule('Pole imię nie może być puste.'),
        new MinLengthRule(3)
    ])
    ->addField('email', 'Email', 'email', [
        new RequiredRule(),
        new EmailRule()
    ])
    ->addTextarea('message', 'Message', 5, 40, [
        new MinLengthRule(10)
    ]);
     ->addFile('avatar', 'Avatar', true , [
        new FileRequiredRule(),
        new FileSizeRule(2 * 1024 * 1024), // 2MB
        new FileMimeTypeRule(['image/jpeg', 'image/png']),
    ]);
```

### 2. Validate data

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($form->validate($_POST, $_FILES)) {
        // Process data (e.g., save to DB, send email)
    }
}
```

### 3. Render form

```php
echo FormRenderer::render($form, $_POST);
```

## ✅ Sample Output

```html
<form action="/submit.php" method="POST">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" value="Jan"><br>
    <div class='error' style='color:red;'>Pole imię nie może być puste.</div>
    <div class='error' style='color:red;'>Name must be at least 3 characters.</div>

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" value="not-an-email"><br>
    <div class='error' style='color:red;'>Email is not valid.</div>

    <label for="message">Message:</label>
    <textarea id="message" name="message" rows="5" cols="40">Hi!</textarea><br>
    <div class='error' style='color:red;'>Message must be at least 10 characters.</div>

    <label for="email">Avatar:</label>
    <input type="file" name="avatar[]" id="avatar"><br>
    <div class='error' style='color:red;'>Avatar contains disallowed file type.</div>

    <input type="submit" name="send" value="Save">
</form>
```

## 🧪 Running Tests

```bash
php bin/phpunit tests/
```

Includes tests for:

- Input rendering
- Validation errors
- Rule logic (email, numeric, minlength, maxlength, required)

## 📁 Project Structure

```
src/
└── Form/
    ├── FormBuilder.php
    ├── FormRenderer.php
    └── Validator/
        ├── ValidationRule.php
        ├── RequiredRule.php
        ├── EmailRule.php
        ├── MinLengthRule.php
        ├── MaxLengthRule.php
        └── NumericRule.php

tests/
└── Form/
    ├── FormBuilderTest.php
    ├── FormRendererTest.php
    └── ValidationRulesTest.php
```

## 📃 License

MIT © 2025