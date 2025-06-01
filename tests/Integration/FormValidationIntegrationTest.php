<?php 

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;
use Tomazo\Form\Validator\FileMimeTypeRule;
use Tomazo\Form\Validator\FileSizeRule;

class FormValidationIntegrationTest extends TestCase
{
    public function testValidFileUpload(): void
    {
        $form = (new FormBuilder())
            ->addFile('upload', 'Upload File', false, [
                new FileMimeTypeRule(['text/plain']),
                new FileSizeRule(2 * 1024 * 1024) // 2 MB
            ]);

            $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
            file_put_contents($tmpFile, 'This is a plain text file');
    
            //simulation of global variable $_FILE
            $files = [
                'upload' => [
                    'name' => ['test.txt'],
                    'type' => ['text/plain'],
                    'tmp_name' => [$tmpFile],
                    'error' => [UPLOAD_ERR_OK],
                    'size' => [1024]
                ]
            ];

        $this->assertTrue($form->validate([], $files));
        $this->assertEmpty($form->getErrors());

        unlink($tmpFile);
    }

    public function testInvalidMimeType(): void
    {
        $form = (new FormBuilder())
        ->addFile('upload', 'Upload File', false, [
            new FileMimeTypeRule(['image/jpg']),
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, 'This is a plain text file');

        $files = [
            'upload' => [
                'name' => ['test.txt'],
                'type' => ['text/plain'],
                'tmp_name' => [$tmpFile],
                'error' => [UPLOAD_ERR_OK],
                'size' => [1024]
            ]
        ];

        $this->assertFalse($form->validate([], $files));
        $this->assertArrayHasKey('upload', $form->getErrors());

        unlink($tmpFile);
    }

    public function testFileTooLarge(): void
    {
        $form = (new FormBuilder())
            ->addFile('upload', 'Upload File', false, [
                new FileSizeRule(1024)
            ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, 'This is a plain text file');
    
        $files = [
            'upload' => [
                'name' => ['test.txt'],
                'type' => ['text/plain'],
                'tmp_name' => [$tmpFile],
                'error' => [UPLOAD_ERR_OK],
                'size' => [3000]
            ]
        ];

        $this->assertFalse($form->validate([], $files));
        $this->assertArrayHasKey('upload', $form->getErrors());
        $this->assertStringContainsString('upload exceeds max size', strtolower(implode('', $form->getErrors()['upload'])));

        unlink($tmpFile);
    }

    public function testMultipleValidFiles(): void
    {
        $form = (new FormBuilder())
            ->addFile('upload', 'Upload File', true, [
                new FileMimeTypeRule(['text/plain']),
                new FileSizeRule(1024 * 1024) // 1 MB
            ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, 'This is a plain text file');

        $files = [
            'upload' => [
                'name' => ['test.txt', 'test2.txt'],
                'type' => ['text/plain', 'text/plain'],
                'tmp_name' => [$tmpFile, $tmpFile],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [3000, 5000]
            ]
        ];

        $this->assertTrue($form->validate([], $files));
        unlink($tmpFile);
    }

    public function testMultipleFilesOneInvalid(): void
    {
        $form = (new FormBuilder())
        ->addFile('images', 'Upload Images', true, [
            new FileMimeTypeRule(['image/jpg']),
        ]);

        $tmpTxtFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpTxtFile, 'This is a plain text file');

        $jpegData = base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDAREAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAb/xAAVAQEBAAAAAAAAAAAAAAAAAAAAAf/aAAwDAQACEAMQAAAB6A=='
        );
        $tmpJpegFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpJpegFile, $jpegData);

        $files = [
            'images' => [
                'name' => ['test.txt', 'image.jpg'],
                'type' => ['text/plain', 'image/jpg'],
                'tmp_name' => [$tmpTxtFile, $tmpJpegFile],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [3000, 5000]
            ]
        ];

        $this->assertFalse($form->validate([], $files));
        $this->assertArrayHasKey('images', $form->getErrors());

        unlink($tmpTxtFile);
        unlink($tmpJpegFile);
    }
}