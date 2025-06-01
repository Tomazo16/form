<?php 

namespace Tomazo\Form\Validator;

class MinLengthRule implements ValidationRule
{
    public function __construct(private int $min, private ?string $message = null)
    {
        
    }
    public function validate(string $fieldName, mixed $value, array $files = []): ?string
    {
        return strlen((string)$value) < $this->min ? $this->message ?? ucfirst($fieldName) . " must be at least {$this->min} characters." : null;
    }
}