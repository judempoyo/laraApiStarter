<?php

declare(strict_types=1);

namespace App\Console\Support;

use Illuminate\Database\Eloquent\Model;

class ModelIntrospector
{
    protected Model $model;

    /** @var array<string> */
    protected array $fillable;

    /** @var array<string, string> */
    protected array $casts;

    /** @var array<string> */
    protected array $hidden;

    /** @var array<string> */
    protected array $excludedFields;

    public function __construct(string $modelClass)
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class [{$modelClass}] does not exist.");
        }

        $this->model = new $modelClass();
        $this->fillable = $this->model->getFillable();
        $this->casts = $this->model->getCasts();
        $this->hidden = $this->model->getHidden();
        $this->excludedFields = config('api-generator.excluded_fields', []);
    }

    /**
     * Resolve the fully qualified model class name.
     */
    public static function resolveModelClass(string $name): ?string
    {
        $namespace = config('api-generator.namespaces.model', 'App\\Models');
        $fqcn = $namespace . '\\' . $name;

        return class_exists($fqcn) ? $fqcn : null;
    }

    /**
     * Get the filterable fields (fillable minus excluded).
     *
     * @return array<string>
     */
    public function getFields(): array
    {
        return array_values(array_diff($this->fillable, $this->excludedFields));
    }

    /**
     * Get fields for resource (fillable minus hidden, plus id and timestamps).
     *
     * @return array<string>
     */
    public function getResourceFields(): array
    {
        $fields = array_diff($this->fillable, $this->hidden);
        $timestampFields = config('api-generator.resource_timestamp_fields', ['created_at', 'updated_at']);

        // Always include id at the beginning
        $result = ['id'];
        foreach ($fields as $field) {
            if (!in_array($field, $this->excludedFields) && $field !== 'id') {
                $result[] = $field;
            }
        }

        // Add timestamps at the end
        foreach ($timestampFields as $ts) {
            $result[] = $ts;
        }

        return array_unique($result);
    }

    /**
     * Generate DTO property lines.
     */
    public function generateDtoProperties(): string
    {
        $fields = $this->getFields();

        if (empty($fields)) {
            return "        // TODO: define your properties";
        }

        $lines = [];
        foreach ($fields as $field) {
            $type = $this->mapToPhpType($field);
            $lines[] = "        public {$type} \${$field},";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate DTO fromRequest mapping lines.
     */
    public function generateFromRequestMapping(): string
    {
        $fields = $this->getFields();

        if (empty($fields)) {
            return "            // TODO: map validated data to properties";
        }

        $lines = [];
        foreach ($fields as $field) {
            $lines[] = "            {$field}: \$validated['{$field}'],";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate validation rules for request.
     */
    public function generateRequestRules(): string
    {
        $fields = $this->getFields();

        if (empty($fields)) {
            return "            // TODO: define validation rules";
        }

        $lines = [];
        foreach ($fields as $field) {
            $rules = $this->inferValidationRules($field);
            $rulesStr = implode("', '", $rules);
            $lines[] = "            '{$field}' => ['{$rulesStr}'],";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate validation messages for request.
     */
    public function generateRequestMessages(): string
    {
        $fields = $this->getFields();

        if (empty($fields)) {
            return "            // TODO: define validation messages";
        }

        $lines = [];
        $label = fn (string $f): string => str_replace('_', ' ', $f);

        foreach ($fields as $field) {
            $rules = $this->inferValidationRules($field);
            $humanName = $label($field);

            foreach ($rules as $rule) {
                $message = match ($rule) {
                    'required'   => "The {$humanName} field is required.",
                    'string'     => "The {$humanName} must be a valid string.",
                    'integer'    => "The {$humanName} must be a valid integer.",
                    'numeric'    => "The {$humanName} must be a valid number.",
                    'boolean'    => "The {$humanName} must be true or false.",
                    'email'      => "The {$humanName} must be a valid email address.",
                    'date'       => "The {$humanName} must be a valid date.",
                    'max:255'    => "The {$humanName} may not be greater than 255 characters.",
                    'array'      => "The {$humanName} must be an array.",
                    default      => null,
                };

                if ($message !== null) {
                    $lines[] = "            '{$field}.{$rule}' => '{$message}',";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Generate resource field mapping lines.
     */
    public function generateResourceFields(): string
    {
        $fields = $this->getResourceFields();

        if (empty($fields)) {
            return "            // TODO: define resource fields";
        }

        $maxLen = max(array_map('strlen', $fields));
        $lines = [];
        foreach ($fields as $field) {
            $padded = str_pad("'{$field}'", $maxLen + 2);
            $lines[] = "            {$padded} => \$this->{$field},";
        }

        return implode("\n", $lines);
    }

    /**
     * Map a model field to its PHP type based on casts.
     */
    protected function mapToPhpType(string $field): string
    {
        $cast = $this->casts[$field] ?? null;

        if ($cast === null) {
            // Infer from field name conventions
            if (str_ends_with($field, '_id')) {
                return 'int';
            }
            if (str_starts_with($field, 'is_') || str_starts_with($field, 'has_')) {
                return 'bool';
            }
            if (str_ends_with($field, '_at')) {
                return 'string';
            }
            if ($field === 'email') {
                return 'string';
            }

            return 'string';
        }

        return match (true) {
            $cast === 'integer', $cast === 'int'          => 'int',
            $cast === 'float', $cast === 'double'         => 'float',
            $cast === 'boolean', $cast === 'bool'         => 'bool',
            $cast === 'array', $cast === 'json'           => 'array',
            $cast === 'datetime', $cast === 'date'        => 'string',
            $cast === 'hashed'                            => 'string',
            str_contains($cast, 'decimal:')               => 'float',
            enum_exists($cast)                            => 'string',
            default                                       => 'string',
        };
    }

    /**
     * Infer validation rules from field name and cast type.
     */
    protected function inferValidationRules(string $field): array
    {
        $cast = $this->casts[$field] ?? null;
        $rules = ['required'];

        // Email field
        if ($field === 'email' || str_ends_with($field, '_email')) {
            return [...$rules, 'string', 'email', 'max:255'];
        }

        // Name-like string fields
        if (in_array($field, ['name', 'first_name', 'last_name', 'title', 'slug', 'label'])) {
            return [...$rules, 'string', 'max:255'];
        }

        // Foreign keys
        if (str_ends_with($field, '_id')) {
            return [...$rules, 'integer'];
        }

        // Boolean fields
        if (str_starts_with($field, 'is_') || str_starts_with($field, 'has_') || $cast === 'boolean' || $cast === 'bool') {
            return [...$rules, 'boolean'];
        }

        // Cast-based rules
        if ($cast !== null) {
            return match (true) {
                $cast === 'integer', $cast === 'int'                => [...$rules, 'integer'],
                $cast === 'float', $cast === 'double',
                str_contains($cast, 'decimal:')                     => [...$rules, 'numeric'],
                $cast === 'datetime', $cast === 'date'              => [...$rules, 'date'],
                $cast === 'array', $cast === 'json'                 => [...$rules, 'array'],
                enum_exists($cast)                                  => [...$rules, 'string'],
                default                                             => [...$rules, 'string', 'max:255'],
            };
        }

        // Default: string
        return [...$rules, 'string', 'max:255'];
    }
}
