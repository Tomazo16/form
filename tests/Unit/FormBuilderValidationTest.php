<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;
use Tomazo\Form\Validator\NumericRule;
use Tomazo\Form\Validator\EmailRule;

class FormBuilderValidationTest extends TestCase
{
    public function testValidationFailsWhenInvalidDataProvided()
    {
        $form = (new FormBuilder())
            ->addField('email', 'Email', 'text', [new EmailRule()])
            ->addField('age', 'Wiek', 'text', [new NumericRule()]);

        $data = ['email' => 'not-an-email', 'age' => 'abc'];

        $isValid = $form->validate($data);
        $errors = $form->getErrors();

        $this->assertFalse($isValid);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('age', $errors);
        $this->assertContains('Email must be a valid email address.', $errors['email']);
        $this->assertContains('Age must be a numeric value.', $errors['age']);
    }

    public function testValidationPassesWhenValidDataProvided()
    {
        $form = (new FormBuilder())
            ->addField('email', 'Email', 'text', [new EmailRule()])
            ->addField('age', 'Age', 'text', [new NumericRule()]);

        $data = ['email' => 'test@example.com', 'age' => 30];

        $isValid = $form->validate($data);

        $this->assertTrue($isValid);
        $this->assertEmpty($form->getErrors());
    }
}
