<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Utils\FormUtils;
use Tomazo\Form\Utils\UploadHandler;

class FormUtilsMoveFilesUnitTest extends TestCase
{
    private string $uploadDir;

    protected function setUp(): void
    {
        $this->uploadDir = sys_get_temp_dir() . '/uploads_' . uniqid();
        mkdir($this->uploadDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->uploadDir)) {
            foreach (glob("{$this->uploadDir}/*") as $file) {
                unlink($file);
            }
            rmdir($this->uploadDir);
        }
    }

    public function testSingleFileUpload()
    {
        // Create dummy file
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tmpFile, 'file content');

        $files = [
            'avatar' => [
                [
                    'name' => 'user.jpg',
                    'tmp_name' => $tmpFile,
                    'error' => UPLOAD_ERR_OK,
                    'type' => 'image/jpeg',
                    'size' => 1234,
                ]
            ]
        ];

        $fields = [
            'avatar' => [
                'type' => 'file',
                'directory' => $this->uploadDir,
            ],
        ];

        $fakeHandler = new class extends UploadHandler {
            public function isUploadedFile(string $tmpName): bool
            {
                return file_exists($tmpName); // simulate "uploaded" file
            }

            public function moveUploadedFile(string $tmpName, string $target): bool
            {
                return rename($tmpName, $target); // simulate "move_uploaded_file"
            }
        };

        $moved = FormUtils::moveFiles($fields, $files, $fakeHandler);

        $this->assertArrayHasKey('avatar', $moved);
        $this->assertCount(1, $moved['avatar']);
        $this->assertFileExists($moved['avatar'][0]);
        $this->assertSame('file content', file_get_contents($moved['avatar'][0]));
    }

    public function testMultipleFilesUpload()
    {
        // Simulate two uploaded files
        $tmpFile1 = tempnam(sys_get_temp_dir(), 'upload_');
        $tmpFile2 = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tmpFile1, 'first');
        file_put_contents($tmpFile2, 'second');

        $files = [
            'docs' => [
                [
                    'name' => 'file1.txt',
                    'tmp_name' => $tmpFile1,
                    'error' => UPLOAD_ERR_OK,
                ],
                [
                    'name' => 'file2.txt',
                    'tmp_name' => $tmpFile2,
                    'error' => UPLOAD_ERR_OK,
                ],
            ],
        ];

        $fields = [
            'docs' => [
                'type' => 'file',
                'directory' => $this->uploadDir,
            ],
        ];

        $fakeHandler = new class extends UploadHandler {
            public function isUploadedFile(string $tmpName): bool
            {
                return file_exists($tmpName);
            }

            public function moveUploadedFile(string $tmpName, string $target): bool
            {
                return rename($tmpName, $target);
            }
        };

        $moved = FormUtils::moveFiles($fields, $files, $fakeHandler);

        $this->assertArrayHasKey('docs', $moved);
        $this->assertCount(2, $moved['docs']);
        foreach ($moved['docs'] as $path) {
            $this->assertFileExists($path);
        }
    }
}
