<?php 

namespace Tomazo\Form;

use Tomazo\Form\Utils\FormUtils;
use Tomazo\Form\Utils\PathResolverInterface;
use Tomazo\Form\Utils\UploadHandler;
use Tomazo\Form\Utils\UploadPathResolver;
use Tomazo\Form\Validator\ValidationRule;

class FormBuilder
{
    private array $fields = [];
    private array $errors = [];
    private ?array $files = null;
    private array $formData; // raw data from Form
    private array $data = []; // entity data converted to arrays
    private ?PathResolverInterface $pathResolver = null;

    public function __construct(
        private string $action = '',
        private string $method = 'POST',
    )
    {
        
    }

    // setup PathResolver when you need replace it or set $baseDir, default we create UploadPathResolver in method move() with default settings
    public function setPathResolver(PathResolverInterface $pathResolver): void
    {
        $this->pathResolver = $pathResolver;
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

    public function addFile(string $name, string $label,bool $multiple, array $rules = [], ?string $directory = null, bool $overwrite = false):self
    {
        $this->fields[$name] = ['label' => $label, 'type' => 'file', 'multiple' => $multiple, 'rules' => $rules, 'directory' => $directory, 'overwrite' => $overwrite];
        return $this;
    }

    public function validate(array $data, array $files= []): bool
    {
        //normalize  $_FILES
        $this->files = FormUtils::normalizeFiles($files);

        foreach ($this->fields as $name => $field) {
            $value = $data[$name] ?? null;

            //set raw Form Data
            $this->formData[$name] = $value;

            foreach ($field['rules']  as $rule) {
                if ($rule instanceof ValidationRule) {
                    $error = $rule->validate($name, $value, $this->files);
                    if ($error !== null) {
                        $this->errors[$name][] = $error;
                    }
                }
            }
        }

        return count($this->errors) > 0 ? false : true;
    }

    public function bind(object $source): void
    {
        $data = [];

        foreach (array_keys($this->fields) as $name) {
            $getter = 'get' . ucfirst($name);
            $getterIs = 'is' . ucfirst($name);

            $value = match (true) {
                method_exists($source, $getter) => $source->$getter(),
                method_exists($source, $getterIs) => $source->$getterIs(),
                property_exists($source, $name) => $source->$name,
                default => null
            };

            if ($value !== null) {
                $data[$name] = $value;
            }
        }
        
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function move(): ?array
    {
        if ($this->files === null) {
            throw new \RuntimeException("You must call validate() before move()");
        }

        $moved =  FormUtils::moveFiles($this->fields, $this->files, new UploadHandler(), $this->pathResolver ?? new UploadPathResolver());
                
        return empty($moved) ? null : $moved;
    }

    public function moveReplacing(array $oldFiles): ?array
    {
        if ($this->files === null) {
            throw new \RuntimeException("You must call validate() before moveReplacing()");
        }

        $pathResolver = $this->pathResolver ?? new UploadPathResolver();

        $moved =  FormUtils::moveFiles($this->fields, $this->files, new UploadHandler(), $pathResolver);

    
        if (!empty($moved)) {
            FormUtils::removeFile($oldFiles, $moved, $pathResolver);
    
            return $moved;
        }
    
        return null;
    }

    public function get(string $name): string
    {   
        if (!array_key_exists($name, $this->formData)) {
            throw new \LogicException("There is not key {$name} in Form Data");
        }

        return htmlspecialchars($this->formData[$name]);
    }

    public function getFields(): array
    {
        return $this->fields;
    }
    
    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
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