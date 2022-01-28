<?php
namespace Evas\Db\Builders\Traits;

trait DateBasedWhereTrait
{
    protected function addDateBasedWhere(
        bool $isOr, string $date_operator, string $column, $operator, $value = null
    ) {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 4
        );
        return $this->pushWhere(
            'DateBased', compact('column', 'operator', 'date_operator', 'value', 'isOr')
        );
    }

    protected function addBetweenDateBasedWhere(
        bool $isOr, string $date_operator, string $column, array $values
    ) {
        return $this->pushWhere(
            'BetweenDateBased', compact('column', 'date_operator', 'values', 'isOr')
        );
    }

    // Or/And Where Date/Year/Month/Day/Time/Hour/Minute/Second

    public function whereDate(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(false, 'DATE', ...func_get_args());
    }

    public function orWhereDate(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(true, 'DATE', ...func_get_args());
    }

    public function whereYear(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(false, 'YEAR', ...func_get_args());
    }

    public function orWhereYear(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(true, 'YEAR', ...func_get_args());
    }

    public function whereMonth(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(false, 'MONTH', ...func_get_args());
    }

    public function orWhereMonth(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(true, 'MONTH', ...func_get_args());
    }

    public function whereDay(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(false, 'DAY', ...func_get_args());
    }

    public function orWhereDay(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(true, 'DAY', ...func_get_args());
    }

    public function whereTime(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(false, 'TIME', ...func_get_args());
    }

    public function orWhereTime(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(true, 'TIME', ...func_get_args());
    }

    public function whereHour(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(false, 'HOUR', ...func_get_args());
    }

    public function orWhereHour(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(true, 'HOUR', ...func_get_args());
    }

    public function whereMinute(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(false, 'MINUTE', ...func_get_args());
    }

    public function orWhereMinute(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(true, 'MINUTE', ...func_get_args());
    }

    public function whereSecond(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(false, 'SECOND', ...func_get_args());
    }

    public function orWhereSecond(string $column, $operator, $value = null)
    {
        return $this->addDateBasedWhere(true, 'SECOND', ...func_get_args());
    }

    // And/Or Where Between Date/Year/Month/Day/Time/Hour/Minute/Second

    public function whereDateBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'DATE', $column, $values);
    }

    public function orWhereDateBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'DATE', $column, $values);
    }

    public function whereYearBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'YEAR', $column, $values);
    }

    public function orWhereYearBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'YEAR', $column, $values);
    }

    public function whereMonthBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'MONTH', $column, $values);
    }

    public function orWhereMonthBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'MONTH', $column, $values);
    }

    public function whereDayBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'DAY', $column, $values);
    }

    public function orWhereDayBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'DAY', $column, $values);
    }

    public function whereTimeBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'TIME', $column, $values);
    }

    public function orWhereTimeBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'TIME', $column, $values);
    }

    public function whereHourBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'HOUR', $column, $values);
    }

    public function orWhereHourBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'HOUR', $column, $values);
    }

    public function whereMinuteBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'MINUTE', $column, $values);
    }

    public function orWhereMinuteBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'MINUTE', $column, $values);
    }

    public function whereSecondBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(false, 'SECOND', $column, $values);
    }

    public function orWhereSecondBetween(string $column, array $values)
    {
        return $this->addBetweenDateBasedWhere(true, 'SECOND', $column, $values);
    }
}
