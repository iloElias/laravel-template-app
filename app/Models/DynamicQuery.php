<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class DynamicQuery extends Model
{
    public static function searchWhereAllColluns($query, $modelInstance, $whereJoin = []): mixed
    {
        $search = Request::get('search');

        if (! $search) {
            return $query;
        }

        $fillable = $modelInstance->getFillable();

        $query->where(function ($query) use ($search, $fillable, $modelInstance, $whereJoin) {
            foreach ($fillable as $column) {
                $query->orWhere($modelInstance->getTable().'.'.$column, 'ilike', "%{$search}%");
            }

            foreach ($whereJoin as $value) {
                $query->orWhere($value, 'ilike', "%{$search}%");
            }
        });

        return $query;
    }

    public static function orderBy( $query): mixed
    {
        $order = Request::get('order');
        $orderType = Request::get('orderType', 'asc');

        return $order ? $query->orderBy($order, $orderType) : $query;
    }

    public static function paginate($query): mixed
    {
        $page = Request::get('page');
        $limit = (int) Request::get('limit', 30);

        return $page ? $query->paginate($limit) : $query->get();
    }

    /**
     * Apply limit and offset from the current request to the query.
     * Use: Model::query()->withPagination()->get()
     */
    public function scopeWithPagination(Builder $query, int $defaultLimit = 15): Builder
    {
        return $query
            ->limit(request()->integer('limit', $defaultLimit))
            ->offset(request()->integer('offset', 0));
    }
}