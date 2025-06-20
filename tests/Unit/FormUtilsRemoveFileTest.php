<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Utils\FormUtils;
use Tomazo\Form\Utils\UploadPathResolver;
use Tomazo\Form\Exception\FileDeletionException;

class FormUtilsRemoveFileTest extends TestCase
{
    private string $uploadDir;

    protected function setUp(): void
    {
        $this->uploadDir = sys_get_temp_dir() . '/form_test_' . uniqid();
        mkdir($this->uploadDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->uploadDir)) {
            foreach (glob("{$this->uploadDir}/*") as $file) {
                @unlink($file);
            }
            @rmdir($this->uploadDir);
        }
    }

    public function testRemoveFileDeletesSuccessfully(): void
    {
        $file = $this->uploadDir . '/oldfile.txt';
        file_put_contents($file, 'test');

        $resolver = new UploadPathResolver($this->uploadDir);

        $old = ['avatar' => ['oldfile.txt']];
        $moved = ['avatar' => ['newfile.jpg']];

        $this->assertFileExists($file);

        FormUtils::removeFile($old, $moved, $resolver);

        $this->assertFileDoesNotExist($file);
    }

    public function testRemoveFileThrowsExceptionWhenUnlinkFails(): void
    {
        $file = $this->uploadDir . '/not_deletable.txt';
        file_put_contents($file, 'test');
        chmod($file, 0444); // make file read-only

        $resolver = new UploadPathResolver($this->uploadDir);

        $old = ['avatar' => ['not_deletable.txt']];
        $moved = ['avatar' => ['newfile.jpg']];

        $this->expectException(FileDeletionException::class);
        $this->expectExceptionMessageMatches('/Failed to remove file/');

        try {
            FormUtils::removeFile($old, $moved, $resolver);
        } finally {
            chmod($file, 0644); // restore so tearDown can remove
        }
    }

    public function testRemoveFileDoesNothingWhenFileMissing(): void
    {
        $resolver = new UploadPathResolver($this->uploadDir);

        $old = ['avatar' => ['non_existing_file.txt']];
        $moved = ['avatar' => ['newfile.jpg']];

        // Should not throw even if file doesn't exist
        FormUtils::removeFile($old, $moved, $resolver);

        $this->assertTrue(true); // dummy assert to mark test as passed
    }

    public function testRemoveFileSkipsUnrelatedFields(): void
    {
        $file = $this->uploadDir . '/should_stay.txt';
        file_put_contents($file, 'data');

        $resolver = new UploadPathResolver($this->uploadDir);

        $old = ['not_avatar' => ['should_stay.txt']];
        $moved = ['avatar' => ['newfile.jpg']];

        FormUtils::removeFile($old, $moved, $resolver);

        $this->assertFileExists($file);
    }
}
