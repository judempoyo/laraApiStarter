<?php

declare(strict_types=1);

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validResources = array_keys(config('import.resources', []));

        return [
            'file'     => [
                'required',
                'file',
                'max:10240', // 10 MB max
                'mimetypes:text/csv,text/plain,application/json,application/csv,text/comma-separated-values',
            ],
            'resource' => ['required', 'string', Rule::in($validResources)],
            'dry_run'  => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'     => 'An import file is required.',
            'file.file'         => 'The uploaded item must be a valid file.',
            'file.max'          => 'The file size must not exceed 100 MB.',
            'file.mimetypes'    => 'The file must be a CSV or JSON file.',
            'resource.required' => 'An import resource is required.',
            'resource.in'       => 'The selected resource is not available for import.',
        ];
    }

    /**
     * Determine if the requested resource requires admin privileges.
     */
    public function resourceIsAdminOnly(): bool
    {
        $resources = config('import.resources', []);
        $class     = $resources[$this->input('resource')] ?? null;

        if (! $class || ! class_exists($class)) {
            return false;
        }

        return (bool) app($class)->isAdminOnly();
    }
}
