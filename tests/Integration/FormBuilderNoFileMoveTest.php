<?php 

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;
use Tomazo\Form\Utils\UploadPathResolver;
use Tomazo\Form\Validator\FileRequiredRule;

class FormBuilderNoFileMoveTest extends TestCase
{
    public function testMoveReturnsNullWhenNoFileWasUploaded(): void
    {
        // Przykładowy $_FILES bez faktycznego pliku
        $files = [
            'img_src' => [
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0,
            ],
        ];

        $form = new FormBuilder();
        $pathResolver = new UploadPathResolver(sys_get_temp_dir());
        $form->setPathResolver($pathResolver);
        $form->addFile('img_src', 'Image', false, [], 'test');

        // Walidacja
        $isValid = $form->validate([], $files);
        $this->assertTrue($isValid, 'Validation should pass when file is optional');

        // move() powinno zwrócić null, bo nie było pliku
        $result = $form->move();
        $this->assertNull($result, 'move() should return null when no file is uploaded');
    }
}
