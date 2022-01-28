<?php
namespace Evas\Db\Builders\Traits;

trait WhereBetweenTrait
{
    // Or/And Where Between/Not Between

    public function whereBetween(string $column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $values = array_slice($values, 0, 2);
        return $this->pushWhere('Between', compact('column', 'values', 'isOr', 'isNot'));
    }

    public function orWhereBetween(string $column, array $values)
    {
        return $this->between($column, $values, true);
    }

    public function whereNotBetween(string $column, array $values)
    {
        return $this->between($column, $values, false, true);
    }

    public function orWhereNotBetween(string $column, array $values)
    {
        return $this->between($column, $values, true, true);
    }

    // Or/And Where Between/Not Between Columns

    public function whereBetweenColumns(string $column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $columns = array_slice($values, 0, 2);
        return $this->pushWhere('BetweenColumns', compact('column', 'columns', 'isOr', 'isNot'));
    }

    public function orWhereBetweenColumns(string $column, array $values)
    {
        return $this->between($column, $values, true);
    }

    public function whereNotBetweenColumns(string $column, array $values)
    {
        return $this->between($column, $values, false, true);
    }

    public function orWhereNotBetweenColumns(string $column, array $values)
    {
        return $this->between($column, $values, true, true);
    }
}
