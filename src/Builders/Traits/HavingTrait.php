<?php
/**
 * Трейт сборки HAVING части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\HavingOption;

trait HavingTrait
{
    /** @var array HAVING часть */
    public $havings = [];

    public function havingRaw(string $sql, array $bindings = null)
    {
        $this->havings[] = HavingOption::raw($sql, $bindings);
        return $this;
    }

    public function orHavingRaw(string $sql, array $bindings = null)
    {
        $this->havings[] = HavingOption::raw($sql, $bindings, true);
        return $this;
    }


    public function having($column, $operator = null, $value = null)
    {
        $this->havings[] = HavingOption::single(false, ...func_get_args());
        return $this;
    }

    public function orHaving($column, $operator = null, $value = null)
    {
        $this->havings[] = HavingOption::single(true, ...func_get_args());
        return $this;
    }


    // Null

    public function havingNull($column)
    {
        $this->havings[] = HavingOption::null($column, false, false);
        return $this;
    }

    public function orHavingNull($column)
    {
        $this->havings[] = HavingOption::null($column, true, false);
        return $this;
    }

    public function havingNotNull($column)
    {
        $this->havings[] = HavingOption::null($column, false, true);
        return $this;
    }

    public function orHavingNotNull($column)
    {
        $this->havings[] = HavingOption::null($column, true, true);
        return $this;
    }


    // Between

    public function havingBetween($column, $values)
    {
        $this->havings[] = HavingOption::between($column, $values, false, false);
        return $this;
    }

    public function orHavingBetween($column, $values)
    {
        $this->havings[] = HavingOption::between($column, $values, true, false);
        return $this;
    }

    public function havingNotBetween($column, $values)
    {
        $this->havings[] = HavingOption::between($column, $values, false, true);
        return $this;
    }

    public function orHavingNotBetween($column, $values)
    {
        $this->havings[] = HavingOption::between($column, $values, true, true);
        return $this;
    }


    // Between columns

    public function havingBetweenColumns($column, $columns)
    {
        $this->havings[] = HavingOption::betweenColumns($column, $columns, false, false);
        return $this;
    }

    public function orHavingBetweenColumns($column, $columns)
    {
        $this->havings[] = HavingOption::betweenColumns($column, $columns, true, false);
        return $this;
    }

    public function havingNotBetweenColumns($column, $columns)
    {
        $this->havings[] = HavingOption::betweenColumns($column, $columns, false, true);
        return $this;
    }

    public function orHavingNotBetweenColumns($column, $columns)
    {
        $this->havings[] = HavingOption::betweenColumns($column, $columns, true, true);
        return $this;
    }


    // Aggregates

    public function havingAggregate(string $function, string $column, $operator, $value = null)
    {
        $this->havings[] = HavingOption::aggregate(false, ...func_get_args());
        return $this;
    }

    public function orHavingAggregate(string $function, string $column, $operator, $value = null)
    {
        $this->havings[] = HavingOption::aggregate(true, ...func_get_args());
        return $this;
    }
}
