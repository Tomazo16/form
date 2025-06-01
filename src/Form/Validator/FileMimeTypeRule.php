<?php 

namespace Tomazo\Form\Validator;

class FileMimeTypeRule implements ValidationRule
{
    public function __construct(private array $allowedMimeTypes, private ?string $message = null)
    {
        
    }

    public function validate(string $fieldName, mixed $value, array $files = []): ?string
    {
        if (!isset($files[$fieldName])) {
            return null;
        }

        foreach ($files[$fieldName] as $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                return !in_array($mime, $this->allowedMimeTypes) ? $this->message ?? ucfirst($fieldName) ." contains disallowed file type." : null;
            }
        }

        return null;
    }
}