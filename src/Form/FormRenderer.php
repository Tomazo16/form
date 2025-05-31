<?php 

namespace Tomazo\Form;

class FormRenderer
{
    public static function render(FormBuilder $formBuilder, array $values = []): string
    {
        $errors = $formBuilder->getErrors();

        $html = "<form action='{$formBuilder->getAction()}' method='{$formBuilder->getMethod()}'>";

        foreach ($formBuilder->getFields() as $name => $field) {
            $label = htmlspecialchars($field['label']);
            $value = htmlspecialchars($values[$name] ?? '');

            $html .= "<label for='{$name}'>{$label}:</label>";

            $html .= match ($field['type']) {
                'textarea' => self::renderTextarea($name, $field, $value),
                'select' => self::renderSelect($name, $field, $value),
                'radio' => self::renderRadio($name, $field, $value),
                default => "<input type='{$field['type']}' name='{$name}' id='{$name}' value='{$value}'><br>",
            };

            if(key_exists($name, $errors)) {
                foreach ($errors[$name] as $error) {
                    $html .= "<div class='error' style='color:red;'>{$error}</div>";
                }
            }
        }

        $html .= "<input type='submit' name='send' value='Save'>";
        $html .= "</form>";

        return $html;
    }

    private static function renderTextarea(string $name, array $field, string $value): string
    {
        return "<textarea id='{$name}' name='{$name}' rows='{$field['rows']}' cols='{$field['cols']}'>" .
            htmlspecialchars($value) . "</textarea><br>";
    }

    private static function renderSelect(string $name, array $field, string $selectedValue = ''): string
    {
        $html = "<select name='{$name}' id='{$name}'>";
        foreach ($field['options'] as $value => $label) {
            $safeValue = htmlspecialchars($value);
            $safeLabel = htmlspecialchars($label);
            $selected = ($value === $selectedValue) ? " selected" : "";
            $html .= "<option value='{$safeValue}'{$selected}>{$safeLabel}</option>";
        }
        $html .= "</select><br>";

        return $html;
    }

    private static function renderRadio(string $name, array $field, string $selectedId = ''): string
    {
        $html = '';
        foreach ($field['options'] as $id => $label) {
            $safeId = htmlspecialchars($id);
            $safeLabel = htmlspecialchars($label);
            $inputId = $name . '_' . $safeId;
            $checked = ((string)$id === (string)$selectedId) ? " checked" : "";

            $html .= "<input type='radio' id='{$inputId}' name='{$name}' value='{$safeId}'{$checked}> ";
            $html .= "<label for='{$inputId}'>{$safeLabel}</label><br>";
        }

        return $html;
    }
}