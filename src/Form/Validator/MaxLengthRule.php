<?php 

namespace Tomazo\Form\Validator;

class MaxLengthRule implements ValidationRule
{
    public function __construct(private int $max, private ?string $message = null)
    {
        
    }
    public function validate(string $fieldName, mixed $value, array $files = []): ?string
    {
        return strlen((string)$value) > $this->max ? $this->message ?? ucfirst($fieldName) . " must be at most {$this->max} characters." : null;
    }
}