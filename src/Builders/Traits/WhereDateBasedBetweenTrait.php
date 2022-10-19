<?php
/**
 * Трейт сборки WHERE части between со столбцом с функцией даты.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\WhereOption;

trait WhereDateBasedBetweenTrait
{
    public function whereDateBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(false, 'DATE', $column, $values);
        return $this;
    }

    public function orWhereDateBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(true, 'DATE', $column, $values);
        return $this;
    }

    public function whereYearBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(false, 'YEAR', $column, $values);
        return $this;
    }

    public function orWhereYearBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(true, 'YEAR', $column, $values);
        return $this;
    }

    public function whereMonthBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(false, 'MONTH', $column, $values);
        return $this;
    }

    public function orWhereMonthBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(true, 'MONTH', $column, $values);
        return $this;
    }

    public function whereDayBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(false, 'DAY', $column, $values);
        return $this;
    }

    public function orWhereDayBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(true, 'DAY', $column, $values);
        return $this;
    }

    public function whereTimeBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(false, 'TIME', $column, $values);
        return $this;
    }

    public function orWhereTimeBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(true, 'TIME', $column, $values);
        return $this;
    }

    public function whereHourBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(false, 'HOUR', $column, $values);
        return $this;
    }

    public function orWhereHourBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(true, 'HOUR', $column, $values);
        return $this;
    }

    public function whereMinuteBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(false, 'MINUTE', $column, $values);
        return $this;
    }

    public function orWhereMinuteBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(true, 'MINUTE', $column, $values);
        return $this;
    }

    public function whereSecondBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(false, 'SECOND', $column, $values);
        return $this;
    }

    public function orWhereSecondBetween(string $column, array $values)
    {
        $this->wheres[] = WhereOption::dateBasedBetween(true, 'SECOND', $column, $values);
        return $this;
    }
}
