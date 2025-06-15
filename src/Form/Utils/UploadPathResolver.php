<?php 

namespace Tomazo\Form\Utils;

class UploadPathResolver implements PathResolverInterface
{
    private string $baseDir;

    public function __construct(string $baseDir = '')
    {
        if ($baseDir !== '') {
            $real = realpath($baseDir);
            if (!$real) {
                throw new \InvalidArgumentException("Provided base directory '{$baseDir}' is invalid.");
            }
            $this->baseDir = rtrim($real, DIRECTORY_SEPARATOR);
            return;
        }

        $configFile = __DIR__ . '/../../../../../../config/FormConfig.php';
        if (!file_exists($configFile)) {
            throw new \RuntimeException("File FormConfig.php does not exist.");
        }

        $config = require $configFile;
        if (empty($config['baseDir']) || !realpath($config['baseDir'])) {
            throw new \RuntimeException("Invalid or missing 'baseDir' in FormConfig.php.");
        }

        $this->baseDir = rtrim(realpath($config['baseDir']), DIRECTORY_SEPARATOR);
    }

    /**
     * Returns full filesystem path to save the uploaded file.
     */
    public function getTargetPath(string $subdir, string $safeName): string
    {
        // Target path from field definition
        $subdir = trim($subdir, '/\\');
        $fullDir = $this->baseDir . DIRECTORY_SEPARATOR . $subdir;

        // Create directory when they is not exists
        if (!is_dir($fullDir)) {
            if (!mkdir($fullDir, 0777, true) && !is_dir($fullDir)) {
                throw new \RuntimeException("Failed to create directory: {$fullDir}", 500);
            }
        }

        return $fullDir . DIRECTORY_SEPARATOR. $safeName;
    }

    /**
     * Returns relative path to store in the database (e.g., for <img src>)
     */
    public function getRelativePath(string $fullPath): string
    {
        $relative = str_replace($this->baseDir, '', $fullPath);
        $relative = ltrim(str_replace('\\', '/', $relative), '/');

        return $relative;
    }
}