<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Validator\EmailRule;
use Tomazo\Form\Validator\NumericRule;
use Tomazo\Form\Validator\RequiredRule;
use Tomazo\Form\Validator\MinLengthRule;
use Tomazo\Form\Validator\MaxLengthRule;

class ValidationRuleTest extends TestCase
{
    public function testEmailRule()
    {
        $rule = new EmailRule();

        $this->assertNull($rule->validate('email', 'test@example.com'));
        $this->assertEquals('Email must be a valid email address.', $rule->validate('email', 'invalid'));
    }

    public function testNumericRule()
    {
        $rule = new NumericRule();

        $this->assertNull($rule->validate('age', 123));
        $this->assertNull($rule->validate('price', '45.67'));
        $this->assertEquals('Age must be a numeric value.', $rule->validate('age', 'abc'));
    }

    public function testRequiredRule()
    {
        $rule = new RequiredRule();

        $this->assertNull($rule->validate('name', 'John'));
        $this->assertEquals('Name is required.', $rule->validate('name', ''));
        $this->assertEquals('Name is required.', $rule->validate('name', null));
    }

    public function testMinLengthRule()
    {
        $rule = new MinLengthRule(5);

        $this->assertNull($rule->validate('password', '12345'));
        $this->assertEquals('Password must be at least 5 characters.', $rule->validate('password', '123'));
    }

    public function testMaxLengthRule()
    {
        $rule = new MaxLengthRule(10);

        $this->assertNull($rule->validate('username', 'shortname'));
        $this->assertEquals('Username must be at most 10 characters.', $rule->validate('username', 'toolongusername'));
    }
}
