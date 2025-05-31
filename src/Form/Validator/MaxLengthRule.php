<?php 

namespace Tomazo\Form\Validator;

class MaxLengthRule implements ValidationRule
{
    public function __construct(private int $max)
    {
        
    }
    public function validate(string $fieldName, mixed $value): ?string
    {
        return strlen((string)$value) > $this->max ? ucfirst($fieldName) . " must be at most {$this->max} characters." : null;
    }
}