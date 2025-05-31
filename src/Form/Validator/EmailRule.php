<?php 

namespace Tomazo\Form\Validator;

class EmailRule implements ValidationRule
{
    public function validate(string $fieldName, mixed $value): ?string
    {
        return !filter_var($value, FILTER_VALIDATE_EMAIL) ? ucfirst($fieldName) . " must be a valid email address." : null;
    }
}