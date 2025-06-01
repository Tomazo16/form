<?php 

namespace Tomazo\Form\Validator;

class FileRequiredRule implements ValidationRule
{
    public function __construct(private ?string $message = null)
    {
        
    }

    public function validate(string $fieldName, mixed $value, array $files = []): ?string
    {
        $message = $this->message  ?? ucfirst($fieldName) . " is required.";

        if (!isset($files[$fieldName])) {
            return $message;
        }

        return empty(array_filter($files[$fieldName], fn($file) => $file['error'] === UPLOAD_ERR_OK)) ? $message : null;
    }
}