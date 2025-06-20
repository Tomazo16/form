<?php 

namespace Tomazo\Form\Utils;

use Tomazo\Form\Exception\FileDeletionException;
use Tomazo\Form\Exception\FileMovingException;

class FormUtils
{
    public static function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $field => $data) {
            // Checking the completeness of the file structure
            if (!isset($data['name'], $data['type'], $data['tmp_name'], $data['error'], $data['size'])) {
                continue;
            }

            if (is_array($data['name'])) {
                foreach ($data['name'] as $index => $name) {
                    $normalized[$field][$index] = [
                        'name' => $name,
                        'type' => $data['type'][$index],
                        'tmp_name' => $data['tmp_name'][$index],
                        'error' => $data['error'][$index],
                        'size' => $data['size'][$index],
                    ];
                }
            }  else {
                $normalized[$field][] = $data;
            }
        }

        return $normalized;
    }

    
    public static function moveFiles(array $fields, array $files, UploadHandler $uploadHandler, PathResolverInterface $pathResolver): array
    {
        $moved = [];

        foreach ($fields as $fieldName => $field) {
            if (($field['type'] ?? '') !== 'file') {
                continue;
            }

            $uploads = $files[$fieldName] ?? [];

            // Single and multiple file support (always as array)
            foreach ((array) $uploads as $file) {
                
                  // We skip if the file field is empty
                  if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                // Checking array structure or Upload error
                if (!isset($file['tmp_name'], $file['error'], $file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
                    throw new FileMovingException("File upload error for field '{$fieldName}': " . FormUtils::uploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE),400);
                }
                
                // Checking the authenticity of file transfers
                if (!$uploadHandler->isUploadedFile($file['tmp_name'])) {
                    throw new FileMovingException("Possible file upload attack: '{$file['name']}' is not a valid uploaded file.",403);
                }

                // Target path from field definition
                $subdir = $field['directory'] ?? '';

                $safeName = bin2hex(random_bytes(8)) . '_' . basename($file['name']);

                // We get the full path of the file on the disk
                $target = $pathResolver->getTargetPath($subdir, $safeName);

                // Move File
                if (!$uploadHandler->moveUploadedFile($file['tmp_name'], $target)) {
                    throw new FileMovingException("Could not move uploaded file '{$file['name']}' to '{$target}'",500);
                }

                // We return the relative path
                $moved[$fieldName][] = $pathResolver->getRelativePath($target);
            }
        }

        return $moved;
    }

    public static function removeFile(?array $oldFiles, array $moved, PathResolverInterface $pathResolver): void
    {
        
            foreach ($moved as $field => $paths) {
                if (isset($oldFiles[$field])) {
                    foreach ((array) $oldFiles[$field] as $oldPath) {
                        $absolutePath = $pathResolver->getAbsolutePath($oldPath);
                        if (is_file($absolutePath)) {
                            if (!@unlink($absolutePath)) {
                                throw new FileDeletionException("Failed to remove file: {$absolutePath}");
                            }
                        }
                    }
                }
            }
    }

    private static function uploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds the upload_max_filesize.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds the MAX_FILE_SIZE directive.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown upload error.',
        };
    }
}