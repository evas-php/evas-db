<?php
/**
 * Трейт добавления where between по значениям.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereBetweenTrait
{
    /**
     * Добавление where between.
     * По умолчанию склейка через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @param bool использовать ли OR для склейки where
     * @param bool использовать ли NOT перед between
     * @return self
     * @throws \InvalidArgumentException
     */
    public function whereBetween($column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $column = $this->prepareColumn($column);
        $values = array_slice($values, 0, 2);
        if (count($values) < 2) {
            throw new \InvalidArgumentException(sprintf(
                'Argument #2 (array $values) passed to %s() must has 2 values',
                __METHOD__
            ));
        }
        $bindings = $values;
        return $this->pushWhere('Between', compact('column', 'bindings', 'isOr', 'isNot'));
    }

    /**
     * Добавление where OR between.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, true);
    }

    /**
     * Добавление where AND NOT between.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereNotBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, false, true);
    }

    /**
     * Добавление where OR NOT between.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereNotBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, true, true);
    }
}
