<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected Request $request;
    protected Builder $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply all filters from the request to the query builder.
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->filters() as $filter => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (method_exists($this, $filter)) {
                $this->{$filter}($value);
            }
        }

        return $this->applySorting($this->builder);
    }

    /**
     * Get the filters from the request.
     *
     * @return array<string, mixed>
     */
    protected function filters(): array
    {
        return $this->request->all();
    }

    /**
     * Apply sorting from request query params.
     * Usage: ?sort=-created_at,name (prefix with - for descending)
     */
    protected function applySorting(Builder $builder): Builder
    {
        $sort = $this->request->query('sort');

        if (!$sort) {
            return $builder;
        }

        $fields = explode(',', $sort);

        foreach ($fields as $field) {
            $field = trim($field);
            if (str_starts_with($field, '-')) {
                $builder->orderBy(substr($field, 1), 'desc');
            } else {
                $builder->orderBy($field, 'asc');
            }
        }

        return $builder;
    }

    /**
     * Filter by a search term across multiple columns.
     */
    protected function search(string $value, array $columns = ['name']): void
    {
        $this->builder->where(function (Builder $query) use ($value, $columns) {
            foreach ($columns as $i => $column) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $query->{$method}($column, 'LIKE', "%{$value}%");
            }
        });
    }
}
