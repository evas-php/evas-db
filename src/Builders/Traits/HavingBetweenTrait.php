<?php
/**
 * Трейт добавления having between по значениям и полям.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait HavingBetweenTrait
{
    /**
     * Добавление (and) having between.
     * @param string|\Closure|self столбец или подзарос
     * @param array значения [min, max]
     * @param bool|null использовать ли OR для склейки
     * @param bool|null использовать ли NOT
     * @return self
     */
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

    /**
     * Добавление or having between.
     * @param string|\Closure|self столбец или подзарос
     * @param array значения [min, max]
     * @return self
     */
    public function orHavingBetween($column, array $values)
    {
        return $this->havingBetween($column, $values, true, false);
    }

    /**
     * Добавление and having NOT between.
     * @param string|\Closure|self столбец или подзарос
     * @param array значения [min, max]
     * @return self
     */
    public function havingNotBetween($column, array $values)
    {
        return $this->havingBetween($column, $values, false, true);
    }

    /**
     * Добавление or having NOT between.
     * @param string|\Closure|self столбец или подзарос
     * @param array значения [min, max]
     * @return self
     */
    public function orHavingNotBetween($column, array $values)
    {
        return $this->havingBetween($column, $values, true, true);
    }
}
