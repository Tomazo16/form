<?php 

namespace Tomazo\Form\Validator;

interface ValidationRule
{
    public function validate(string $fieldName, mixed $value): ?string;
}