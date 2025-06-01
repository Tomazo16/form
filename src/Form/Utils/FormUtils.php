<?php 

namespace Tomazo\Form\Utils;

class FormUtils
{
    public static function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $field => $data) {
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
}