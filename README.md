# PHP FormBuilder & Renderer

A lightweight, extensible system for building, rendering, and validating HTML forms in PHP.

## 🚀 Features

- Fluent form building interface (`FormBuilder`)
- Form rendering to HTML (`FormRenderer`)
- Validation rule support (e.g., Required, Email, MinLength, MaxLength, Numeric)
- File input and ValidationFile rule support (e.g., MIME Type, File Size)
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

### 1a. Create file FormConfig.php in config folder

```php
//config/FormConfig.php
return [
    'baseDir' => __DIR__ . 'path/To/Files/Folder/'
];
```

This file will be automatically used by the default UploadPathResolver.

### 1b. Create form instance manually with injected PathResolver

If you don’t want to use FormConfig.php (see 1a) or need a custom upload location at runtime, you can manually create a FormBuilder instance and inject a custom PathResolver.

The resolver must implement PathResolverInterface (default is UploadPathResolver):

```php
// src/Form/FormCreator.php
use App\Form\FormBuilder;
use App\Form\Utils\UploadPathResolver;

$form = new FormBuilder();
$formCreator = $form->setPathResolver(new UploadPathResolver(__DIR__ . 'path/To/Files/Folder/')); // default is UploadPathResolver
```

### 2. Create a form

```php
use App\Form\FormBuilder;
use App\Form\FormRenderer;
use App\Form\Validator\{RequiredRule, EmailRule, MinLengthRule};

        // If you used 1a:                   // If you used 1b, continue using $formCreator:
$form = (new FormBuilder('/submit.php')) or $formCreator->setAction('/submit.php')
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
    ],
    '/uploads' // target directory
    );
```

### 3. Validate data without Files

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($form->validate($_POST)) {
        // Process data (e.g., save to DB, send email)
    }
}
```

### 4a. Validate data with Files

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($form->validate($_POST, $_FILES)) {
        if ($file = $form->move()) {
            $account->setImgSrc($file['avatar'][0]); // Returns e.g. ['avatar' => ['/target/path/1234_avatar.jpg']]
        }

        ($file = $form->move()) && $account->setImgSrc($file['avatar'][0]);// shortcut version if one file is moved. moves files to specified locations and return ['avatar'][0] => ['/target/path/1234_avatar.jpg']
        // Process data (e.g., save to DB, send email)
    }
}
```

### 4b. Validate data with Files (move and replacing file)

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($form->validate($_POST, $_FILES)) {
        $oldFiles = $account->getImgSrc();

        if ($file = $form->moveReplacing(['avatar' => [$oldFiles]])) {
            $account->setImgSrc($file['avatar'][0]); // Returns e.g. ['avatar' => ['/target/path/1234_avatar.jpg']]
        }

        ($file = $form->moveReplacing(['avatar' => [$oldFiles]])) && $account->setImgSrc($file['avatar'][0]);// shortcut version if one file is moved. moves files to specified locations and return ['avatar'][0] => ['/target/path/1234_avatar.jpg']
        // Process data (e.g., save to DB, send email)
    }
}
```

🔐 move() uses secure validation is_uploaded_file() and move_uploaded_file().

### 5. Render form

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
- File Rule logic (MIME, size, required)
- Single and multiple file support (multiple)
- Upload error validation (UPLOAD_ERR_*)
- Error handling in moveUploadedFile()
- Unit and integration testing with FormBuilder
- Mocking file uploads and system functions

## 📁 Project Structure

```
├── src/
│ ├── Form/
│ │ ├── FormBuilder.php 
│ │ ├── FormRenderer.php 
│ │ ├── FormInterface.php 
│ │ ├── Utils/
│ │ │ ├── FormUtils.php
│ │ │ └── UploadHandler.php
│ │ └── Validator/
│ │ ├── ValidationRule.php
│ │ ├── RequiredRule.php
│ │ ├── EmailRule.php
│ │ ├── FileRequiredRule.php
│ │ ├── FileMimeTypeRule.php
│ │ ├── MaxLengthRule.php
│ │ ├── MinLengthRule.php
│ │ ├── NumericRule.php
│ │ └── FileSizeRule.php
├── tests/
│ │ ├── Integration
│ │ └── Unit
└── README.md
```

## 📃 License

MIT © 2025