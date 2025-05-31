<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;
use Tomazo\Form\FormRenderer;

class FormRendererUnitTest extends TestCase
{

    public function testRenderTextField()
    {
        $form = (new FormBuilder('/submit', 'POST'))
            ->addField('email', 'Email', 'email');

        $html = FormRenderer::render($form, ['email' => 'test@example.com']);

        $this->assertStringContainsString("type='email'", $html);
        $this->assertStringContainsString("value='test@example.com'", $html);
    }

    public function testRenderTextarea()
    {
        $form = (new FormBuilder())->addTextarea('bio', 'Bio', 5, 40);
        $html = FormRenderer::render($form, ['bio' => 'hello']);

        $this->assertStringContainsString("<textarea id='bio' name='bio' rows='5' cols='40'>hello</textarea>", $html);
    }

    public function testRenderSelect()
    {
        $form = (new FormBuilder())
            ->addSelect('role', 'Role', ['admin' => 'Admin', 'user' => 'User']);

        $html = FormRenderer::render($form, ['role' => 'user']);

        $this->assertStringContainsString("<option value='admin'>Admin</option>", $html);
        $this->assertStringContainsString("<option value='user' selected>User</option>", $html);
    }

    public function testRenderRadio()
    {
        $form = (new FormBuilder())
            ->addRadio('gender', 'Gender', [1 => 'Male', 2 => 'Female']);

        $html = FormRenderer::render($form, ['gender' => 2]);

        $this->assertStringContainsString("value='2' checked", $html);
        $this->assertStringContainsString("<label for='gender_2'>Female</label>", $html);
    }

    public function testFormAttributesAndSubmit()
    {
        $form = new FormBuilder('/save', 'PUT');
        $html = FormRenderer::render($form);

        $this->assertStringContainsString("<form action='/save' method='PUT'>", $html);
        $this->assertStringContainsString("type='submit'", $html);
    }
}
