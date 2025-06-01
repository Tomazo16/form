<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Validator\FileMimeTypeRule;

class FileMimeTypeRuleTest extends TestCase
{
    public function testAllowedMimeType()
    {
        $rule = new FileMimeTypeRule(['image/jpeg']);

        // Minimal JPEG Content
        $jpegData = base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDAREAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAb/xAAVAQEBAAAAAAAAAAAAAAAAAAAAAf/aAAwDAQACEAMQAAAB6A=='
        );
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, $jpegData);

        $files = [
            'image' => [
                ['tmp_name' =>$tmpFile, 'error' => UPLOAD_ERR_OK]
            ]
        ];

        // Mock mime_content_type using built-in or stub for actual use
        $this->assertTrue(function_exists('mime_content_type'));
        $this->assertNull($rule->validate('image', null, $files));
        unlink($tmpFile);
    }

    public function testDisallowedMimeType()
    {
        $rule = new FileMimeTypeRule(['image/png']);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, 'This is a plain text file');

        $files = [
            'image' => [
                ['tmp_name' => $tmpFile, 'error' => UPLOAD_ERR_OK]
            ]
        ];
        
        // mime_content_type will return image/jpeg (assuming real file)
        $this->assertEquals('Image contains disallowed file type.', $rule->validate('image', null, $files));

        unlink($tmpFile);
    }
}
