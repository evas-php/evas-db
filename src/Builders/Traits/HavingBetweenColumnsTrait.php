<?php
/**
 * Трейт добавления having between по значениям и полям.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait HavingBetweenColumnsTrait
{
    /**
     * Добавление (and) having between со значениями столбцов в качестве значений.
     * @param string|\Closure|self столбец или подзапрос
     * @param array столбцы или подзапросы [min, max]
     * @param bool использовать ли OR для склейки
     * @param bool использовать ли NOT
     * @return self
     */
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

    /**
     * Добавление or having between со значениями столбцов в качестве значений.
     * @param string|\Closure|self столбец или подзапрос
     * @param array столбцы или подзапросы [min, max]
     * @return self
     */
    public function orHavingBetweenColumns($column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, true, false);
    }

    /**
     * Добавление and having NOT between со значениями столбцов в качестве значений.
     * @param string|\Closure|self столбец или подзапрос
     * @param array столбцы или подзапросы [min, max]
     * @return self
     */
    public function havingNotBetweenColumns($column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, false, true);
    }

    /**
     * Добавление or having NOT between со значениями столбцов в качестве значений.
     * @param string|\Closure|self столбец или подзапрос
     * @param array столбцы или подзапросы [min, max]
     * @return self
     */
    public function orHavingNotBetweenColumns($column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, true, true);
    }
}
