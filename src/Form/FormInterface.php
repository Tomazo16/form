<?php 

namespace Tomazo\Form;

interface FormInterface
{
    public function createForm(): FormBuilder;
}