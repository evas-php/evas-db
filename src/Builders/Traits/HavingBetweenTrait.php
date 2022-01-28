<?php
namespace Evas\Db\Builders\Traits;

trait HavingBetweenTrait
{
    // Or/And Having Between/Not Between

    public function havingBetween(string $column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $values = array_slice($values, 0, 2);
        return $this->pushHaving('Between', compact('column', 'values', 'isOr', 'isNot'));
    }

    public function orHavingBetween(string $column, array $values)
    {
        return $this->havingBetween($column, $values, true);
    }

    public function havingNotBetween(string $column, array $values)
    {
        return $this->havingBetween($column, $values, false, true);
    }

    public function orHavingNotBetween(string $column, array $values)
    {
        return $this->havingBetween($column, $values, true, true);
    }

    // Or/And Where Between/Not Between Columns

    public function havingBetweenColumns(string $column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $values = array_slice($values, 0, 2);
        return $this->pushHaving('BetweenColumns', compact('column', 'values', 'isOr', 'isNot'));
    }

    public function orHavingBetweenColumns(string $column, array $values)
    {
        return $this->havingBetweenColumns($column, $values, true);
    }

    public function havingNotBetweenColumns(string $column, array $values)
    {
        return $this->havingBetweenColumns($column, $values, false, true);
    }

    public function orHavingNotBetweenColumns(string $column, array $values)
    {
        return $this->havingBetweenColumns($column, $values, true, true);
    }
}
