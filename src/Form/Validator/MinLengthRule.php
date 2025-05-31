<?php 

namespace Tomazo\Form\Validator;

class MinLengthRule implements ValidationRule
{
    public function __construct(private int $min)
    {
        
    }
    public function validate(string $fieldName, mixed $value): ?string
    {
        return strlen((string)$value) < $this->min ? ucfirst($fieldName) . " must be at least {$this->min} characters." : null;
    }
}