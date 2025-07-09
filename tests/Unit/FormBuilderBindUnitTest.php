<?php 

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;

class DummyUser
{
    private string $name = 'John';
    private bool $active = true;
    public int $age = 30;

    public function getName(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}

class FormBuilderBindUnitTest extends TestCase
{
    public function testBindBindsObjectDataToForm(): void
    {
        $user = new DummyUser();

        $form = new FormBuilder('/submit');
        $form->addField('name', 'Name', 'text')
             ->addField('active', 'Active', 'checkbox')
             ->addField('age', 'Age', 'number')
             ->addField('missingField', 'Missing', 'text'); // this should be skipped

        $form->bind($user);

        $expected = [
            'name' => 'John',
            'active' => true,
            'age' => 30
            // 'missingField' is not included because it's neither method nor property
        ];

        $this->assertSame($expected, $form->getData());
    }
}
