<?php

use PHPUnit\Framework\TestCase;
use Tomazo\Form\Utils\FormUtils;

class FormUtilsTest extends TestCase
{
    public function testNormalizeSingleFile()
    {
        $files = [
            'document' => [
                'name' => 'file.pdf',
                'type' => 'application/pdf',
                'tmp_name' => '/tmp/php123',
                'error' => UPLOAD_ERR_OK,
                'size' => 1000
            ]
        ];

        $expected = [
            'document' => [
                [
                    'name' => 'file.pdf',
                    'type' => 'application/pdf',
                    'tmp_name' => '/tmp/php123',
                    'error' => UPLOAD_ERR_OK,
                    'size' => 1000
                ]
            ]
        ];

        $this->assertEquals($expected, FormUtils::normalizeFiles($files));
    }

    public function testNormalizeMultipleFiles()
    {
        $files = [
            'images' => [
                'name' => ['img1.jpg', 'img2.jpg'],
                'type' => ['image/jpeg', 'image/jpeg'],
                'tmp_name' => ['/tmp/phpA', '/tmp/phpB'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [1234, 5678]
            ]
        ];

        $expected = [
            'images' => [
                [
                    'name' => 'img1.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => '/tmp/phpA',
                    'error' => UPLOAD_ERR_OK,
                    'size' => 1234
                ],
                [
                    'name' => 'img2.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => '/tmp/phpB',
                    'error' => UPLOAD_ERR_OK,
                    'size' => 5678
                ]
            ]
        ];

        $this->assertEquals($expected, FormUtils::normalizeFiles($files));
    }

    public function testEmptyFilesArray()
    {
        $files = [];
        $expected = [];
        $this->assertEquals($expected, FormUtils::normalizeFiles($files));
    }
}
