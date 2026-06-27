<?php

declare(strict_types=1);

namespace App\Traits;

use App\Http\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Apply a QueryFilter to this model's query builder.
     *
     * Usage in controller:
     *   Product::filter($filter)->paginate();
     *
     * @param  Builder<static>  $builder
     * @param  QueryFilter  $filter
     * @return Builder<static>
     */
    public function scopeFilter(Builder $builder, QueryFilter $filter): Builder
    {
        return $filter->apply($builder);
    }
}
