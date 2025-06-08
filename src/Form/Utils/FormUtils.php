<?php 

namespace Tomazo\Form\Utils;

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

    
    public static function moveFiles(array $fields, array $files, UploadHandler $uploadHandler): array
    {
        $moved = [];

        foreach ($fields as $fieldName => $field) {
            if (($field['type'] ?? '') !== 'file') {
                continue;
            }

            $uploads = $files[$fieldName] ?? [];

            // Single and multiple file support (always as array)
            foreach ((array) $uploads as $file) {
                if (!isset($file['tmp_name'], $file['error'], $file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException("File upload error for field '{$fieldName}': " . FormUtils::uploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE),400);
                }
                
                // Checking the authenticity of file transfers
                if (!$uploadHandler->isUploadedFile($file['tmp_name'])) {
                    throw new \RuntimeException("Possible file upload attack: '{$file['name']}' is not a valid uploaded file.",403);
                }

                // Target path from field definition
                $dir = rtrim($field['directory'] ?? '', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

                // Create directory when they is not exists
                if (!$dir || !is_dir($dir)) {
                    if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                        throw new \RuntimeException("Failed to create directory: {$dir}", 500);
                    }
                }

                $safeName = bin2hex(random_bytes(8)) . '_' . basename($file['name']);
                $target = $dir . $safeName;

                // Move File
                if (!$uploadHandler->moveUploadedFile($file['tmp_name'], $target)) {
                    throw new \RuntimeException("Could not move uploaded file '{$file['name']}' to '{$target}'",500);
                }

                $moved[$fieldName][] = $target;
            }
        }

        return $moved;
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