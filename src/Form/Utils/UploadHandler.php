<?php 

namespace Tomazo\Form\Utils;

class UploadHandler
{
    public function isUploadedFile(string $tmpName): bool
    {
        return is_uploaded_file($tmpName);
    }
    public function moveUploadedFile(string $tmpNanme, string $target): bool
    {
        return move_uploaded_file($tmpNanme, $target);
    }
}