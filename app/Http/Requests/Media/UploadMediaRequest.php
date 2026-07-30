<?php

declare(strict_types=1);

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'       => ['required', 'file', 'max:10240'], // 10 MB max
            'collection' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'disk'       => ['nullable', 'string', Rule::in(array_keys(config('filesystems.disks', [])))],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'       => 'A file is required.',
            'file.file'           => 'The uploaded item must be a valid file.',
            'file.max'            => 'The file may not be larger than 100 MB.',
            'collection.max'      => 'The collection name cannot exceed 50 characters.',
            'collection.regex'    => 'The collection name may only contain lowercase letters, numbers and underscores.',
            'disk.in'             => 'The selected disk is not configured on this server.',
        ];
    }
}
