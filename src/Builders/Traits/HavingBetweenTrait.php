<?php
/**
 * Трейт добавления having between по значениям и полям.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait HavingBetweenTrait
{
    public function havingBetween($column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $column = $this->prepareColumn($column, 'havings');
        $values = array_slice($values, 0, 2);
        if (count($values) < 2) {
            throw new \InvalidArgumentException(sprintf(
                'Argument #2 (array $values) passed to %s() must has 2 values',
                __METHOD__
            ));
        }
        $bindings = $values;
        return $this->pushHaving('Between', compact('column', 'bindings', 'isOr', 'isNot'));
    }

    public function orHavingBetween($column, array $values)
    {
        return $this->havingBetween($column, $values, true, false);
    }

    public function havingNotBetween($column, array $values)
    {
        return $this->havingBetween($column, $values, false, true);
    }

    public function orHavingNotBetween($column, array $values)
    {
        return $this->havingBetween($column, $values, true, true);
    }


    // Between columns

    public function havingBetweenColumns(
        $column, array $columns, bool $isOr = false, bool $isNot = false
    ) {
        $column = $this->prepareColumn($column, 'havings');
        $columns = array_slice($columns, 0, 2);
        if (count($columns) < 2) {
            throw new \InvalidArgumentException(sprintf(
                'Argument #2 (array $columns) passed to %s() must has 2 values',
                __METHOD__
            ));
        }
        $columns = array_map([$this, 'prepareColumn'], $columns);
        return $this->pushHaving('BetweenColumns', compact('column', 'columns', 'isOr', 'isNot'));
    }

    public function orHavingBetweenColumns($column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, true, false);
    }

    public function havingNotBetweenColumns($column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, false, true);
    }

    public function orHavingNotBetweenColumns($column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, true, true);
    }
}
