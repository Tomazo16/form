<?php 

namespace Tomazo\Form\Validator;

class FileSizeRule implements ValidationRule
{
    public function __construct(private int $maxSizeBytes, private ?string $message = null)
    {
        
    }
    public function validate(string $fieldName, mixed $value, array $files = []): ?string
    {
        if (!isset($files[$fieldName])) {
            return null;
        }

        foreach ($files[$fieldName] as $file) {
           return ($file['error'] === UPLOAD_ERR_OK && $file['size'] > $this->maxSizeBytes) ? $this->message ?? ucfirst($fieldName) . " exceeds max size." : null;
        }

        return null;
    }
}