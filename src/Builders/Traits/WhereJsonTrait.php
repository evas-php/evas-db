<?php
/**
 * Трейт сборки WHERE части json.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\WhereOption;

trait WhereJsonTrait
{
    public function whereJsonContains(string $column, $value)
    {
        $this->wheres[] = WhereOption::jsonContains($column, $value, false, false);
        return $this;
    }

    public function orWhereJsonContains(string $column, $value)
    {
        $this->wheres[] = WhereOption::jsonContains($column, $value, true, false);
        return $this;
    }

    public function whereNotJsonContains(string $column, $value)
    {
        $this->wheres[] = WhereOption::jsonContains($column, $value, false, true);
        return $this;
    }

    public function orWhereNotJsonContains(string $column, $value)
    {
        $this->wheres[] = WhereOption::jsonContains($column, $value, true, true);
        return $this;
    }



    public function whereJsonContainsPathAll(string $column, $value, bool $isOne = false)
    {
        $this->wheres[] = WhereOption::jsonContainsPathAll($column, $value, $isOne, false, false);
        return $this;
    }

    public function orWhereJsonContainsPathAll(string $column, $value, bool $isOne = false)
    {
        $this->wheres[] = WhereOption::jsonContainsPathAll($column, $value, $isOne, true, false);
        return $this;
    }

    public function whereNotJsonContainsPathAll(string $column, $value, bool $isOne = false)
    {
        $this->wheres[] = WhereOption::jsonContainsPathAll($column, $value, $isOne, false, true);
        return $this;
    }

    public function orWhereNotJsonContainsPathAll(string $column, $value, bool $isOne = false)
    {
        $this->wheres[] = WhereOption::jsonContainsPathAll($column, $value, $isOne, true, true);
        return $this;
    }



    public function whereJsonLength(string $column, $operator, $value = null)
    {
        $this->wheres[] = WhereOption::jsonLength(false, ...func_get_args());
        return $this;
    }

    public function orWhereJsonLength(string $column, $operator, $value = null)
    {
        $this->wheres[] = WhereOption::jsonLength(true, ...func_get_args());
        return $this;
    }
}
