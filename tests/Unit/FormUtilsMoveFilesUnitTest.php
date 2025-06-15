<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Utils\FormUtils;
use Tomazo\Form\Utils\UploadHandler;
use Tomazo\Form\Utils\UploadPathResolver;

class FormUtilsMoveFilesUnitTest extends TestCase
{
    private string $uploadBaseDir;

    protected function setUp(): void
    {
        $this->uploadBaseDir = sys_get_temp_dir() . '/uploads_' . uniqid();
        mkdir($this->uploadBaseDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->recursiveRemoveDirectory($this->uploadBaseDir);
    }

    private function recursiveRemoveDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $fullPath = "$dir/$file";
            is_dir($fullPath) ? $this->recursiveRemoveDirectory($fullPath) : unlink($fullPath);
        }
        rmdir($dir);
    }

    public function testSingleFileUpload()
    {
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
                'directory' => 'images',
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

        $resolver = new UploadPathResolver($this->uploadBaseDir);
        $moved = FormUtils::moveFiles($fields, $files, $fakeHandler, $resolver);

        $this->assertArrayHasKey('avatar', $moved);
        $this->assertCount(1, $moved['avatar']);

        $fullPath = $this->uploadBaseDir . DIRECTORY_SEPARATOR . $moved['avatar'][0];
        $this->assertFileExists($fullPath);
        $this->assertSame('file content', file_get_contents($fullPath));
    }

    public function testMultipleFilesUpload()
    {
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
                'directory' => 'docs',
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

        $resolver = new UploadPathResolver($this->uploadBaseDir);
        $moved = FormUtils::moveFiles($fields, $files, $fakeHandler, $resolver);

        $this->assertArrayHasKey('docs', $moved);
        $this->assertCount(2, $moved['docs']);

        foreach ($moved['docs'] as $i => $relPath) {
            $fullPath = $this->uploadBaseDir . DIRECTORY_SEPARATOR . $relPath;
            $this->assertFileExists($fullPath);
            $this->assertSame(
                $i === 0 ? 'first' : 'second',
                file_get_contents($fullPath)
            );
        }
    }
}
