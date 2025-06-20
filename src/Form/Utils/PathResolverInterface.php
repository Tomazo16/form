<?php 

namespace Tomazo\Form\Utils;

interface PathResolverInterface
{
    public function getTargetPath(string $subdir, string $safeName): string;
    public function getRelativePath(string $fullPath): string;
    public function getAbsolutePath(string $relative): string;
}