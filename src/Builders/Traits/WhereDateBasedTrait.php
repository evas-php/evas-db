<?php
/**
 * Трейт сборки WHERE части со столбцом с функцией даты.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereDateBasedTrait
{
    /**
     * Добавление условия базирующегося на дате.
     * @param bool использовать ли OR в качестве разделителя условий
     * @param string функция даты
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    protected function pushWhereDateBased(
        bool $isOr, string $function, $column, string $operator, string $value = null
    ) {
        $column = $this->prepareColumn($column);
        $this->prepareValueAndOperator($value, $operator, func_num_args() === 4);
        $bindings = [$value];
        return $this->pushWhere('DateBased', 
            compact('column', 'operator', 'function', 'bindings', 'isOr')
        );
    }

    /**
     * Добавление условия сопоставления даты через AND.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereDate($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(false, 'DATE', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления даты через OR.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereDate($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(true, 'DATE', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления года через AND.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereYear($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(false, 'YEAR', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления года через OR.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereYear($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(true, 'YEAR', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления месяца через AND.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereMonth($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(false, 'MONTH', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления месяца через OR.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereMonth($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(true, 'MONTH', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления дня через AND.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereDay($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(false, 'DAY', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления дня через OR.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereDay($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(true, 'DAY', ...func_get_args());
    }


    /**
     * Добавление условия сопоставления времени через AND.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereTime($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(false, 'TIME', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления времени через OR.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereTime($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(true, 'TIME', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления часа через AND.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereHour($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(false, 'HOUR', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления часа через OR.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereHour($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(true, 'HOUR', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления минут через AND.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereMinute($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(false, 'MINUTE', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления минут через OR.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereMinute($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(true, 'MINUTE', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления секунд через AND.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereSecond($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(false, 'SECOND', ...func_get_args());
    }

    /**
     * Добавление условия сопоставления секунд через OR.
     * @param string|\Closure|self столбец или подзапрос столбца
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereSecond($column, string $operator, string $value = null)
    {
        return $this->pushWhereDateBased(true, 'SECOND', ...func_get_args());
    }
}
