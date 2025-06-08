<?php 

use PHPUnit\Framework\TestCase;
use Tomazo\Form\FormBuilder;
use Tomazo\Form\Utils\FormUtils;
use Tomazo\Form\Utils\UploadHandler;
use Tomazo\Form\Validator\FileRequiredRule;

final class FormBuilderIntegrationTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/upload_test_' . bin2hex(random_bytes(4));
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->tmpDir}/*"));
        rmdir($this->tmpDir);
    }

    public function testValidateAndMoveFileSuccessfully(): void
    {
            // Create a temporary file as "upload"
        $tmpFile = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($tmpFile, 'fake image content');

        $files = [
            'avatar' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile),
            ],
        ];

        $uploadDir = sys_get_temp_dir() . '/uploads_test/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }

        // 1. Setup FormBuilder
        $form = new FormBuilder();
        $form->addField('avatar', 'Avatar', 'file', [
            'rules' => [],
            'directory' => $uploadDir,
        ]);

        // 2. Validate
        $isValid = $form->validate([], $files);
        $this->assertTrue($isValid);

        // 3. Mock UploadHandler
        $uploadHandler = $this->createMock(UploadHandler::class);
        $uploadHandler->method('isUploadedFile')->willReturn(true);
        $uploadHandler->method('moveUploadedFile')
            ->willReturnCallback(function ($tmpName, $target) {
                return copy($tmpName, $target); // we imitate move_uploaded_file
            });

        // 4. Move ( Simulate $form->move() )
        $moved = FormUtils::moveFiles($form->getFields(), FormUtils::normalizeFiles($files), $uploadHandler);

        // 5. Chceck
        $this->assertArrayHasKey('avatar', $moved);
        $this->assertFileExists($moved['avatar'][0]);

        // 6. Clean Up
        unlink($tmpFile);
        unlink($moved['avatar'][0]);
        rmdir($uploadDir);
    }

    public function testMoveThrowsExceptionIfValidateNotCalled(): void
    {
        $form = new FormBuilder();
        $form->addFile('avatar', 'avatar',false,[],$this->tmpDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("You must call validate() before move()");

        $form->move();
    }

    public function testValidationFailsOnUploadError(): void
    {
        $files = [
            'avatar' => [
                'name' => 'broken.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/fakefile.jpg',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0,
            ]
        ];

        $form = new FormBuilder();
        $form->addFile('avatar', 'avatar',false,[new FileRequiredRule()],$this->tmpDir);

        $valid = $form->validate([], $files);

        $this->assertFalse($valid);
        $this->assertNotEmpty($form->getErrors()['avatar']);
    }
}
