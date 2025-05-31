
<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;
use Tomazo\Form\FormRenderer;
use Tomazo\Form\Validator\NumericRule;
use Tomazo\Form\Validator\EmailRule;

class FormRendererValidationTest extends TestCase
{
    public function testNumericValidationFails()
    {
        $form = (new FormBuilder())
            ->addField('age', 'Age', 'text', [new NumericRule()]);

        $data = ['age' => 'abc']; // invalid value
        $form->validate($data);
        $html = FormRenderer::render($form, $data);

        $this->assertStringContainsString('Age must be a numeric value.', $html);
        $this->assertStringContainsString("name='age' id='age' value='abc'", $html); // input value is preserved
    }

    public function testEmailValidationFails()
    {
        $form = (new FormBuilder())
            ->addField('email', 'Email', 'text', [new EmailRule()]);

        $data = ['email' => 'invalid-email'];
        $form->validate($data);
        $html = FormRenderer::render($form, $data);

        $this->assertStringContainsString('Email must be a valid email address.', $html);
        $this->assertStringContainsString("value='invalid-email'", $html);
    }

    public function testValidationPassesAndNoErrorsRendered()
    {
        $form = (new FormBuilder())
            ->addField('email', 'Email', 'text', [new EmailRule()])
            ->addField('age', 'Age', 'text', [new NumericRule()]);

        $data = ['email' => 'john@example.com', 'age' => 25];
        $form->validate($data);
        $html = FormRenderer::render($form, $data);

        $this->assertStringNotContainsString('error', $html);
        $this->assertStringContainsString("value='25'", $html);
        $this->assertStringContainsString("value='john@example.com'", $html);
    }
}
