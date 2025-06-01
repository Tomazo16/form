<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Validator\FileRequiredRule;

class FileRequiredRuleTest extends TestCase
{
    public function testFileIsPresent()
    {
        $rule = new FileRequiredRule();

        $files = [
            'upload' => [
                ['name' => 'file.txt', 'error' => UPLOAD_ERR_OK]
            ]
        ];

        $this->assertNull($rule->validate('upload', null, $files));
    }

    public function testFileIsMissing()
    {
        $rule = new FileRequiredRule();

        $files = []; // No files uploaded

        $this->assertEquals('Upload is required.', $rule->validate('upload', null, $files));
    }

    public function testFileUploadError()
    {
        $rule = new FileRequiredRule();

        $files = [
            'upload' => [
                ['name' => '', 'error' => UPLOAD_ERR_NO_FILE]
            ]
        ];

        $this->assertEquals('Upload is required.', $rule->validate('upload', null, $files));
    }
}
