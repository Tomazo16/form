<?php 

namespace Tomazo\Form;

use Tomazo\Form\Utils\FormUtils;
use Tomazo\Form\Validator\ValidationRule;

class FormBuilder
{
    private array $fields = [];
    private array $errors = [];

    public function __construct(
        private string $action = '',
        private string $method = 'POST',
    )
    {
        
    }

    public function addField(string $name, string $label, string $type = 'text', array $rules = []): self
    {
        $this->fields[$name]= ['label' => $label, 'type' => $type, 'rules' => $rules];
        return $this;
    }

    public function addTextarea(string $name, string $label, int $rows, int $cols, array $rules = []): self
    {
        $this->fields[$name] = ['label' => $label, 'type' => 'textarea' ,'rows' => $rows, 'cols' => $cols, 'rules' => $rules];
        return $this;
    }

    public function addSelect(string $name, string $label, array $options, array $rules = []): self
    {
        $this->fields[$name] = ['label' => $label, 'type' => 'select', 'options' => $options, 'rules' => $rules];
        return $this;
    }

    public function addRadio(string $name, string $label, array $options, array $rules = []): self
    {
        $this->fields[$name] = ['label' => $label, 'type' => 'radio', 'options' => $options, 'rules' => $rules];
        return $this;
    }

    public function addFile(string $name, string $label,bool $multiple, array $rules = []):self
    {
        $this->fields[$name] = ['label' => $label, 'type' => 'file', 'multiple' => $multiple, 'rules' => $rules];
        return $this;
    }

    public function validate(array $data, array $files= []): bool
    {
        //normalize  $_FILES
        $files = FormUtils::normalizeFiles($files);

        foreach ($this->fields as $name => $field) {
            $value = $data[$name] ?? null;

            foreach ($field['rules']  as $rule) {
                if ($rule instanceof ValidationRule) {
                    $error = $rule->validate($name, $value, $files);
                    if ($error !== null) {
                        $this->errors[$name][] = $error;
                    }
                }
            }
        }

        return count($this->errors) > 0 ? false : true;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
    
    public function getAction(): string
    {
        return $this->action;
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}