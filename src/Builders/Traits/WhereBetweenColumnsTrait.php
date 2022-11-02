<?php
/**
 * Трейт добавления where between по столбцам или подзапросам.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereBetweenColumnsTrait
{
    /**
     * Добавление where between со значениями столбцов в качестве значений.
     * По умолчанию склейка через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array столбцы или подзапросы из которых достать значение [min, max]
     * @param bool использовать ли OR для склейки where
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function whereBetweenColumns(
        $column, array $columns, bool $isOr = false, bool $isNot = false
    ) {
        $column = $this->prepareColumn($column);
        $columns = array_slice($columns, 0, 2);
        if (count($columns) < 2) {
            throw new \InvalidArgumentException(sprintf(
                'Argument #2 (array $columns) passed to %s() must has 2 values',
                __METHOD__
            ));
        }
        $columns = array_map([$this, 'prepareColumn'], $columns);
        return $this->pushWhere('BetweenColumns', compact('column', 'columns', 'isOr', 'isNot'));
    }

    /**
     * Добавление where OR between со значениями столбцов в качестве значений.
     * @param string|\Closure|self столбец или подзапрос
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function orWhereBetweenColumns($column, array $columns)
    {
        return $this->whereBetweenColumns($column, $columns, true);
    }

    /**
     * Добавление where AND NOT between со значениями столбцов в качестве значений.
     * @param string|\Closure|self столбец или подзапрос
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function whereNotBetweenColumns($column, array $columns)
    {
        return $this->whereBetweenColumns($column, $columns, false, true);
    }

    /**
     * Добавление where OR NOT between со значениями столбцов в качестве значений.
     * @param string|\Closure|self столбец или подзапрос
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function orWhereNotBetweenColumns($column, array $columns)
    {
        return $this->whereBetweenColumns($column, $columns, true, true);
    }
}
