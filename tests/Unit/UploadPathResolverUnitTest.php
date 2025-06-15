<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Utils\UploadPathResolver;

class UploadPathResolverUnitTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir() . '/upload_test_' . uniqid();
        mkdir($this->baseDir . '/images', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->baseDir);
    }

    public function testGetTargetPathAndRelativePath(): void
    {
        // We create a temporary configuration file
        $configPath = $this->baseDir . '/FormConfig.php';
        file_put_contents($configPath, "<?php return ['baseDir' => '{$this->baseDir}'];");

        // Fake __DIR__ - we replace __DIR__ for test config
        $resolver = new UploadPathResolver($this->baseDir);

        $safeName = 'file_test.png';
        $subdir = 'images';
        $targetPath = $resolver->getTargetPath($subdir, $safeName);
        $relativePath = $resolver->getRelativePath($targetPath);

        $expectedTargetPath = $this->baseDir . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR . $safeName;
        $expectedRelativePath = $subdir . '/' . $safeName;

        $this->assertSame(realpath($expectedTargetPath), realpath($targetPath));
        $this->assertEquals($expectedRelativePath, $relativePath);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
