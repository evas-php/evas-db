<?php
/**
 * Трейт сборки WHERE части со столбцом с функцией даты.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\WhereOption;

trait WhereDateBasedTrait
{
    public function whereDate(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(false, 'DATE', ...func_get_args());
        return $this;
    }

    public function orWhereDate(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(true, 'DATE', ...func_get_args());
        return $this;
    }

    public function whereYear(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(false, 'YEAR', ...func_get_args());
        return $this;
    }

    public function orWhereYear(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(true, 'YEAR', ...func_get_args());
        return $this;
    }

    public function whereMonth(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(false, 'MONTH', ...func_get_args());
        return $this;
    }

    public function orWhereMonth(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(true, 'MONTH', ...func_get_args());
        return $this;
    }

    public function whereDay(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(false, 'DAY', ...func_get_args());
        return $this;
    }

    public function orWhereDay(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(true, 'DAY', ...func_get_args());
        return $this;
    }

    public function whereTime(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(false, 'TIME', ...func_get_args());
        return $this;
    }

    public function orWhereTime(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(true, 'TIME', ...func_get_args());
        return $this;
    }

    public function whereHour(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(false, 'HOUR', ...func_get_args());
        return $this;
    }

    public function orWhereHour(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(true, 'HOUR', ...func_get_args());
        return $this;
    }

    public function whereMinute(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(false, 'MINUTE', ...func_get_args());
        return $this;
    }

    public function orWhereMinute(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(true, 'MINUTE', ...func_get_args());
        return $this;
    }

    public function whereSecond(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(false, 'SECOND', ...func_get_args());
        return $this;
    }

    public function orWhereSecond(string $column, string $operator, string $value = null)
    {
        $this->wheres[] = WhereOption::dateBased(true, 'SECOND', ...func_get_args());
        return $this;
    }
}
