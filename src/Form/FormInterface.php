<?php 

namespace Tomazo\Form;

interface FormInterface
{
    public static function createForm(): FormBuilder;
}