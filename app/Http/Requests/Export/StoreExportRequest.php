<?php

declare(strict_types=1);

namespace App\Http\Requests\Export;

use App\Enums\ExportFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validFormats    = array_column(ExportFormat::cases(), 'value');
        $validResources  = array_keys(config('export.resources', []));

        return [
            'resource'         => ['required', 'string', Rule::in($validResources)],
            'format'           => ['required', 'string', Rule::in($validFormats)],

            // Dynamic filters — all optional
            'filters'                => ['nullable', 'array'],
            'filters.user_id'        => ['nullable', 'integer', 'exists:users,id'],
            'filters.date_from'      => ['nullable', 'date'],
            'filters.date_to'        => ['nullable', 'date', 'after_or_equal:filters.date_from'],
            'filters.status'         => ['nullable', 'string', 'max:50'],
            'filters.role'           => ['nullable', 'string', 'max:50'],
            'filters.expired'        => ['nullable', 'boolean'],
            'filters.unread_only'    => ['nullable', 'boolean'],
            // ID-based scoping (works without dates)
            'filters.ids'            => ['nullable', 'array'],
            'filters.ids.*'          => ['integer', 'min:1'],
            'filters.id_from'        => ['nullable', 'integer', 'min:1'],
            'filters.id_to'          => ['nullable', 'integer', 'min:1', 'gte:filters.id_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'resource.required'         => 'An export resource is required.',
            'resource.in'               => 'The selected resource is not available for export.',
            'format.required'           => 'An export format is required.',
            'format.in'                 => 'The format must be one of: csv, json, xlsx.',
            'filters.user_id.exists'    => 'The specified user does not exist.',
            'filters.date_from.date'    => 'The start date must be a valid date.',
            'filters.date_to.date'      => 'The end date must be a valid date.',
            'filters.date_to.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }

    /**
     * Determine if the requested resource requires admin privileges.
     */
    public function resourceIsAdminOnly(): bool
    {
        $resources = config('export.resources', []);
        $class     = $resources[$this->input('resource')] ?? null;

        if (! $class || ! class_exists($class)) {
            return false;
        }

        return (bool) app($class)->isAdminOnly();
    }
}
