<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Validator\FileSizeRule;

class FileSizeRuleTest extends TestCase
{
    public function testValidFileSize()
    {
        $rule = new FileSizeRule(1024); // 1 KB

        $files = [
            'upload' => [
                ['size' => 512, 'error' => UPLOAD_ERR_OK]
            ]
        ];

        $this->assertNull($rule->validate('upload', null, $files));
    }

    public function testFileTooLarge()
    {
        $rule = new FileSizeRule(1024); // 1 KB

        $files = [
            'upload' => [
                ['size' => 2048, 'error' => UPLOAD_ERR_OK]
            ]
        ];

        $this->assertEquals('Upload exceeds max size.', $rule->validate('upload', null, $files));
    }
}
