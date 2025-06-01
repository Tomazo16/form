<?php 

namespace Tomazo\Form\Validator;

class NumericRule implements ValidationRule
{
    public function __construct(private ?string $message = null)
    {
        
    }
    public function validate(string $fieldName, mixed $value, array $files = []): ?string
    {
        return !is_numeric($value) ? $this->message ?? ucfirst($fieldName) . " must be a numeric value." : null;
    }
}