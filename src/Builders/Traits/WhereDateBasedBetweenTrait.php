<?php
/**
 * Трейт сборки WHERE части between со столбцом с функцией даты.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereDateBasedBetweenTrait
{
    /**
     * Добавление between условия базирующегося на дате.
     * @param bool использовать ли OR в качестве разделителя условий
     * @param string функция даты
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     * @throws \InvalidArgumentException
     */
    protected function pushWhereDateBasedBetween(
        bool $isOr, string $function, $column, array $values
    ) {
        $column = $this->prepareColumn($column);
        $values = array_slice($values, 0, 2);
        if (count($values) < 2) {
            throw new \InvalidArgumentException(sprintf(
                'Argument #2 (array $values) passed to %s() must has 2 values',
                __METHOD__
            ));
        }
        $bindings = $values;
        return $this->pushWhere(
            'BetweenDateBased', compact('column', 'function', 'bindings', 'isOr')
        );
    }

    /**
     * Добавление условия соответствия даты диапазону значений через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereDateBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(false, 'DATE', $column, $values);
    }

    /**
     * Добавление условия соответствия даты диапазону значений через OR.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereDateBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(true, 'DATE', $column, $values);
    }

    /**
     * Добавление условия соответствия года диапазону значений через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereYearBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(false, 'YEAR', $column, $values);
    }

    /**
     * Добавление условия соответствия года диапазону значений через OR.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereYearBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(true, 'YEAR', $column, $values);
    }

    /**
     * Добавление условия соответствия месяца диапазону значений через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereMonthBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(false, 'MONTH', $column, $values);
    }

    /**
     * Добавление условия соответствия месяца диапазону значений через OR.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereMonthBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(true, 'MONTH', $column, $values);
    }

    /**
     * Добавление условия соответствия дня диапазону значений через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereDayBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(false, 'DAY', $column, $values);
    }

    /**
     * Добавление условия соответствия дня диапазону значений через OR.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereDayBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(true, 'DAY', $column, $values);
    }


    /**
     * Добавление условия соответствия времени диапазону значений через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereTimeBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(false, 'TIME', $column, $values);
    }

    /**
     * Добавление условия соответствия времени диапазону значений через OR.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereTimeBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(true, 'TIME', $column, $values);
    }

    /**
     * Добавление условия соответствия часов диапазону значений через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereHourBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(false, 'HOUR', $column, $values);
    }

    /**
     * Добавление условия соответствия часов диапазону значений через OR.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereHourBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(true, 'HOUR', $column, $values);
    }

    /**
     * Добавление условия соответствия минут диапазону значений через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereMinuteBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(false, 'MINUTE', $column, $values);
    }

    /**
     * Добавление условия соответствия минут диапазону значений через OR.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereMinuteBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(true, 'MINUTE', $column, $values);
    }

    /**
     * Добавление условия соответствия секунд диапазону значений через AND.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function whereSecondBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(false, 'SECOND', $column, $values);
    }

    /**
     * Добавление условия соответствия секунд диапазону значений через OR.
     * @param string|\Closure|self столбец или подзапрос
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereSecondBetween($column, array $values)
    {
        return $this->pushWhereDateBasedBetween(true, 'SECOND', $column, $values);
    }
}
