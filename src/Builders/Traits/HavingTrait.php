<?php
namespace Evas\Db\Builders\Traits;

trait HavingTrait
{
    // And/Or Having Raw

    public function havingRaw(string $sql, array $values = [], bool $isOr = false)
    {
        return $this->pushHaving('Raw', compact('sql', 'values', 'isOr'));
    }

    public function orHavingRaw(string $sql, array $values = [])
    {
        return $this->havingRaw($sql, $values);
    }

    // And/Or Having

    protected function addHaving(bool $isOr, string $column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 3
        );
        return $this->pushHaving('Single', compact('column', 'operator', 'value', 'isOr'));
    }

    public function having(string $column, $operator, $value = null)
    {
        return $this->addHaving(false, ...func_get_args());
    }

    public function orHaving(string $column, $operator, $value = null)
    {
        return $this->addHaving(true, ...func_get_args());
    }

    // And/Or Having Is Null/Not Null

    public function havingNull(string $column, bool $isOr = false, bool $isNot = false)
    {
        return $this->pushHaving('Null', compact('column', 'isOr', 'isNot'));
    }

    public function orHavingNull(string $column)
    {
        return $this->havingNull($column, true);
    }

    public function HavingNotNull(string $column)
    {
        return $this->havingNull($column, false, true);
    }

    public function orHavingNotNull(string $column)
    {
        return $this->havingNull($column, true, true);
    }
}
