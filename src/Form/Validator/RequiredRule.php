<?php 

namespace Tomazo\Form\Validator;

class RequiredRule implements ValidationRule
{
    public function __construct(private ?string $message = null)
    {
        
    }
    public function validate(string $fieldName, mixed $value, array $files = []): ?string
    {
        return empty($value) ? $this->message ?? ucfirst($fieldName) . " is required." : null;
    }
}