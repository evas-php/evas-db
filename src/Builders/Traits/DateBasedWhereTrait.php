<?php
/**
 * Трейт сборки условий базирующихся на дате.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait DateBasedWhereTrait
{
    /**
     * Добавление условия базирующегося на дате.
     * @param bool использовать ли OR в качестве разделителя условий
     * @param string оператор даты
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    protected function addDateBasedWhere(
        bool $isOr, string $date_operator, string $column, string $operator, string $value = null
    ) {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 4
        );
        return $this->pushWhere(
            'DateBased', compact('column', 'operator', 'date_operator', 'value', 'isOr')
        );
    }

    /**
     * Добавление between условия базирующегося на дате.
     * @param bool использовать ли OR в качестве разделителя условий
     * @param string оператор даты
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    protected function addBetweenDateBasedWhere(
        bool $isOr, string $date_operator, string $column, array $values
    ) {
        return $this->pushWhere(
            'BetweenDateBased', compact('column', 'date_operator', 'values', 'isOr')
        );
    }


    // ----------
    // Or/And Where Date/Year/Month/Day/Time/Hour/Minute/Second
    // ----------


    /**
     * Добавления условия сопоставления даты через AND.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereDate(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(false, 'DATE', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления даты через OR.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereDate(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(true, 'DATE', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления года через AND.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereYear(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(false, 'YEAR', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления года через OR.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereYear(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(true, 'YEAR', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления месяца через AND.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereMonth(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(false, 'MONTH', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления месяца через OR.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereMonth(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(true, 'MONTH', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления дня через AND.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereDay(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(false, 'DAY', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления дня через OR.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereDay(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(true, 'DAY', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления времени через AND.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereTime(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(false, 'TIME', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления времени через OR.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereTime(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(true, 'TIME', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления часа через AND.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereHour(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(false, 'HOUR', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления часа через OR.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereHour(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(true, 'HOUR', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления минут через AND.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereMinute(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(false, 'MINUTE', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления минут через OR.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereMinute(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(true, 'MINUTE', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления секунд через AND.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function whereSecond(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(false, 'SECOND', ...func_get_args());
    }

    /**
     * Добавления условия сопоставления секунд через OR.
     * @param string столбец
     * @param string оператор или значение
     * @param string|null значение или null
     * @return self
     */
    public function orWhereSecond(string $column, string $operator, string $value = null)
    {
        return $this->addDateBasedWhere(true, 'SECOND', ...func_get_args());
    }


    // ----------
    // And/Or Where Date/Year/Month/Day/Time/Hour/Minute/Second Between
    // ----------


    /**
     * Добавления условия соответствия даты диапазону значений через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereDateBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'DATE', $column, $values);
    }

    /**
     * Добавления условия соответствия даты диапазону значений через OR.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereDateBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'DATE', $column, $values);
    }

    /**
     * Добавления условия соответствия года диапазону значений через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereYearBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'YEAR', $column, $values);
    }

    /**
     * Добавления условия соответствия года диапазону значений через OR.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereYearBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'YEAR', $column, $values);
    }

    /**
     * Добавления условия соответствия месяца диапазону значений через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereMonthBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'MONTH', $column, $values);
    }

    /**
     * Добавления условия соответствия месяца диапазону значений через OR.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereMonthBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'MONTH', $column, $values);
    }

    /**
     * Добавления условия соответствия дня диапазону значений через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereDayBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'DAY', $column, $values);
    }

    /**
     * Добавления условия соответствия дня диапазону значений через OR.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereDayBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'DAY', $column, $values);
    }

    /**
     * Добавления условия соответствия времени диапазону значений через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereTimeBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'TIME', $column, $values);
    }

    /**
     * Добавления условия соответствия времени диапазону значений через OR.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereTimeBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'TIME', $column, $values);
    }

    /**
     * Добавления условия соответствия часа диапазону значений через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereHourBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'HOUR', $column, $values);
    }

    /**
     * Добавления условия соответствия часа диапазону значений через OR.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereHourBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'HOUR', $column, $values);
    }

    /**
     * Добавления условия соответствия минут диапазону значений через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereMinuteBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'MINUTE', $column, $values);
    }

    /**
     * Добавления условия соответствия минут диапазону значений через OR.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereMinuteBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'MINUTE', $column, $values);
    }

    /**
     * Добавления условия соответствия секунд диапазону значений через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereSecondBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'SECOND', $column, $values);
    }

    /**
     * Добавления условия соответствия секунд диапазону значений через OR.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereSecondBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'SECOND', $column, $values);
    }
}
