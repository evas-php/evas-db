<?php
/**
 * Трейт сборки WHERE части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\WhereOption;

trait WhereTrait
{
    /** @var array WHERE часть */
    public $wheres = [];

    public function whereRaw(string $sql, array $bindings = null)
    {
        $this->wheres[] = WhereOption::raw($sql, $bindings);
        return $this;
    }

    public function orWhereRaw(string $sql, array $bindings = null)
    {
        $this->wheres[] = WhereOption::raw($sql, $bindings, true);
        return $this;
    }


    public function where($column, $operator = null, $value = null)
    {
        $this->wheres[] = WhereOption::single(false, ...func_get_args());
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        $this->wheres[] = WhereOption::single(true, ...func_get_args());
        return $this;
    }


    public function whereColumn($first, $operator = null, $second = null)
    {
        $this->wheres[] = WhereOption::singleColumn(false, ...func_get_args());
        return $this;
    }

    public function orWhereColumn($first, $operator = null, $second = null)
    {
        $this->wheres[] = WhereOption::singleColumn(true, ...func_get_args());
        return $this;
    }


    // Sub

    public function whereSub(string $column, $operator, $queryable = null)
    {
        $this->wheres[] = WhereOption::sub(false, ...func_get_args());
        return $this;
    }

    public function orWhereSub(string $column, $operator, $queryable = null)
    {
        $this->wheres[] = WhereOption::sub(true, ...func_get_args());
        return $this;
    }


    // Sub column

    public function whereSubColumn($queryable, $operator, $value = null)
    {
        $this->wheres[] = WhereOption::subColumn(false, ...func_get_args());
        return $this;
    }

    public function orWhereSubColumn($queryable, $operator, $value = null)
    {
        $this->wheres[] = WhereOption::subColumn(true, ...func_get_args());
        return $this;
    }


    // Nested

    public function whereNested($queryable)
    {
        $this->wheres[] = WhereOption::nested($queryable, false);
        return $this;
    }

    public function orWhereNested($queryable)
    {
        $this->wheres[] = WhereOption::nested($queryable, true);
        return $this;
    }


    // Exists

    public function whereExists($queryable)
    {
        $this->wheres[] = WhereOption::exists($queryable, false, false);
        return $this;
    }

    public function orWhereExists($queryable)
    {
        $this->wheres[] = WhereOption::exists($queryable, true, false);
        return $this;
    }

    public function whereNotExists($queryable)
    {
        $this->wheres[] = WhereOption::exists($queryable, false, true);
        return $this;
    }

    public function orWhereNotExists($queryable)
    {
        $this->wheres[] = WhereOption::exists($queryable, true, true);
        return $this;
    }


    // Null

    public function whereNull($column)
    {
        $this->wheres[] = WhereOption::null($column, false, false);
        return $this;
    }

    public function orWhereNull($column)
    {
        $this->wheres[] = WhereOption::null($column, true, false);
        return $this;
    }

    public function whereNotNull($column)
    {
        $this->wheres[] = WhereOption::null($column, false, true);
        return $this;
    }

    public function orWhereNotNull($column)
    {
        $this->wheres[] = WhereOption::null($column, true, true);
        return $this;
    }


    // In

    public function whereIn(string $column, $values)
    {
        $this->wheres[] = WhereOption::in($column, $values, false, false);
        return $this;
    }

    public function orWhereIn(string $column, $values)
    {
        $this->wheres[] = WhereOption::in($column, $values, true, false);
        return $this;
    }

    public function whereNotIn(string $column, $values)
    {
        $this->wheres[] = WhereOption::in($column, $values, false, true);
        return $this;
    }

    public function orWhereNotIn(string $column, $values)
    {
        $this->wheres[] = WhereOption::in($column, $values, true, true);
        return $this;
    }


    // Between

    public function whereBetween($column, $values)
    {
        $this->wheres[] = WhereOption::between($column, $values, false, false);
        return $this;
    }

    public function orWhereBetween($column, $values)
    {
        $this->wheres[] = WhereOption::between($column, $values, true, false);
        return $this;
    }

    public function whereNotBetween($column, $values)
    {
        $this->wheres[] = WhereOption::between($column, $values, false, true);
        return $this;
    }

    public function orWhereNotBetween($column, $values)
    {
        $this->wheres[] = WhereOption::between($column, $values, true, true);
        return $this;
    }


    // Between columns

    public function whereBetweenColumns($column, $columns)
    {
        $this->wheres[] = WhereOption::betweenColumns($column, $columns, false, false);
        return $this;
    }

    public function orWhereBetweenColumns($column, $columns)
    {
        $this->wheres[] = WhereOption::betweenColumns($column, $columns, true, false);
        return $this;
    }

    public function whereNotBetweenColumns($column, $columns)
    {
        $this->wheres[] = WhereOption::betweenColumns($column, $columns, false, true);
        return $this;
    }

    public function orWhereNotBetweenColumns($column, $columns)
    {
        $this->wheres[] = WhereOption::betweenColumns($column, $columns, true, true);
        return $this;
    }
}