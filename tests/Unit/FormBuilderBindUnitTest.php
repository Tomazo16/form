<?php 

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;

class DummyUser
{
    public $name = 'John';
    public $age = 30;
    private $secret = 'top secret';
    protected $hiddenProp = 'hidden';
    public $email = 'john@example.com';

    public function getName()
    {
        return 'John via getter';
    }

    // public isX method
    public function isActive()
    {
        return true;
    }

    // private getter should be ignored
    private function getSecret()
    {
        return $this->secret;
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
            'name' => 'John via getter',  // value z getName()
            'active' => true,             // value z isActive()
            'age' => 30                  // public  property
            // 'missingField' it does not exist in the object, so it is not in the data
        ];

        $this->assertSame($expected, $form->getData());
    }
}
