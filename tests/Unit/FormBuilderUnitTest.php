<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;

class FormBuilderUnitTest extends TestCase
{
    public function testAddFieldStoresCorrectly()
    {
        $form = new FormBuilder('/submit', 'POST');
        $form->addField('username', 'Username', 'text');

        $fields = $form->getFields();

        $this->assertArrayHasKey('username', $fields);
        $this->assertEquals('Username', $fields['username']['label']);
        $this->assertEquals('text', $fields['username']['type']);
    }

    public function testAddTextarea()
    {
        $form = new FormBuilder();
        $form->addTextarea('bio', 'Biography', 5, 40);

        $field = $form->getFields()['bio'];

        $this->assertEquals('textarea', $field['type']);
        $this->assertEquals(5, $field['rows']);
        $this->assertEquals(40, $field['cols']);
    }

    public function testAddSelect()
    {
        $form = new FormBuilder();
        $form->addSelect('role', 'Role', ['admin' => 'Admin', 'user' => 'User']);

        $field = $form->getFields()['role'];

        $this->assertEquals('select', $field['type']);
        $this->assertArrayHasKey('admin', $field['options']);
    }

    public function testAddRadio()
    {
        $form = new FormBuilder();
        $form->addRadio('gender', 'Gender', ['m' => 'Male', 'f' => 'Female']);

        $field = $form->getFields()['gender'];

        $this->assertEquals('radio', $field['type']);
        $this->assertEquals('Male', $field['options']['m']);
    }
}
