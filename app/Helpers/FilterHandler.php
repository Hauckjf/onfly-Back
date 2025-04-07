<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use BadMethodCallException;

class FilterHandler
{
    /**
     * Apply filters to the query builder
     * @param Builder $query
     * @param mixed $filterParams
     * @return Builder
     * @throws InvalidArgumentException|BadMethodCallException
     */
    public function applyFilter(Builder $query, $filterParams): Builder
    {
        if (empty($filterParams)) {
            return $query;
        }

        if (is_string($filterParams)) {
            $filterParams = json_decode($filterParams, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON filter params');
            }
        }

        if (!is_array($filterParams)) {
            throw new InvalidArgumentException('Filter params must be an array or JSON string');
        }

        foreach ($filterParams as $field => $filter) {
            if (!isset($filter['filterType'])) {
                continue;
            }

            $method = $filter['filterType'];
            $field = strtolower($field);

            if (!method_exists($this, $method)) {
                throw new BadMethodCallException("Filter method {$method} does not exist");
            }

            $value = $filter['filterValue'] ?? null;
            $dotPosition = strrpos($field, '.');

            if ($dotPosition !== false) {
                $relationName = substr($field, 0, $dotPosition);
                $relationField = substr($field, $dotPosition + 1);
                $query->whereHas($relationName, function ($subQuery) use ($relationField, $method, $value) {
                    $this->$method($subQuery, $relationField, $value);
                });
            } elseif ($method === 'ILIKE_OR') {
                if (!isset($value['fields']) || !isset($value['value'])) {
                    throw new InvalidArgumentException('ILIKE_OR filter requires fields and value');
                }
                $query->where(function ($query) use ($value, $method) {
                    foreach ($value['fields'] as $item) {
                        $this->$method($query, $item, $value['value']);
                    }
                });
            } else {
                $query = $this->$method($query, $field, $value);
            }
        }

        return $query;
    }

    /**
     * Apply ordering to the query builder
     * @param Builder $query
     * @param mixed $orderByParams
     * @return Builder
     */
    public function applyOrder(Builder $query, $orderByParams): Builder
    {
        if (is_string($orderByParams)) {
            $orderByParams = json_decode($orderByParams, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $query;
            }
        }

        if (!is_array($orderByParams)) {
            return $query;
        }

        $validDirections = ['asc', 'desc'];

        foreach ($orderByParams as $field => $direction) {
            $field = strtolower($field);
            $direction = strtolower($direction);

            if (!in_array($direction, $validDirections)) {
                $direction = 'desc';
            }

            $query->orderBy($field, $direction);
        }

        return $query;
    }

    /**
     * Case-insensitive LIKE filter
     */
    private function ILIKE(Builder $query, string $field, string $value): Builder
    {
        return $query->where($field, 'LIKE', '%'.strtolower($value).'%');
    }

    /**
     * Case-insensitive OR LIKE filter
     */
    private function ILIKE_OR(Builder $query, string $field, string $value): Builder
    {
        return $query->orWhere($field, 'LIKE', '%'.strtolower($value).'%');
    }

    /**
     * Case-insensitive LIKE filter (alias)
     */
    private function LIKE_CI(Builder $query, string $field, string $value): Builder
    {
        return $this->ILIKE($query, $field, $value);
    }

    /**
     * Apply an EQUALS filter to the given field
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function EQUALS($query, $field, $value)
    {
        return $query->where($field, '=', $value);
    }

    /**
     * Apply a GREATER_THAN filter to the given field
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function GREATER_THAN($query, $field, $value)
    {
        return $query->where($field, '>', $value);
    }

    /**
     * Apply a LESS_THAN filter to the given field
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function LESS_THAN($query, $field, $value)
    {
        return $query->where($field, '<', $value);
    }

    /**
     * Apply a range filter to the given field with a minimum and maximum value
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @param mixed $range
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function VALUE_RANGE($query, $field, $value)
    {
        return $query->whereBetween($field, [$value['min'], $value['max']]);
    }

    /**
     * Apply a date time range filter to the given field with a start and end date
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @param mixed $range
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function DATE_TIME_RANGE($query, $field, $value)
    {
        $startDate = Carbon::parse($value['startDate'], 'America/Sao_Paulo')->setTimezone('UTC')->toDateTimeString();
        $endDate = Carbon::parse($value['endDate'], 'America/Sao_Paulo')->setTimezone('UTC')->toDateTimeString();

        return $query->whereBetween($field, [$startDate, $endDate]);
    }

    /**
     * Apply a date range filter to the given field with a start and end date
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @param mixed $range
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function DATE_RANGE($query, $field, $value)
    {
        return $query->whereBetween($field, [$value['startDate'], $value['endDate']]);
    }

    /**
     * Apply an IN_ARRAY filter to the given field with an array of values
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @param mixed $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function IN_ARRAY($query, $field, $values)
    {
        return $query->whereIn($field, $values);
    }

    /**
     * Apply a NULL filter to the given field
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function NULL($query, $field)
    {
        return $query->whereNull($field);
    }

    /**
     * Apply a NOT_NULL filter to the given field
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function NOT_NULL($query, $field)
    {
        return $query->whereNotNull($field);
    }

    /**
     * Apply a filter to check the absence of a relationship
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $field
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function RELATION_DOESNT_EXIST($query, $field)
    {
        return $query->doesntHave($field);
    }
}
