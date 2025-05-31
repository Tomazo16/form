<?php 

namespace Tomazo\Form\Validator;

class NumericRule implements ValidationRule
{
    public function validate(string $fieldName, mixed $value): ?string
    {
        return !is_numeric($value) ? ucfirst($fieldName) . " must be a numeric value." : null;
    }
}