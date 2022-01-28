<?php
namespace Evas\Db\Builders\Traits;

trait WhereRowValuesTrait
{
    public function whereRowValues(array $columns, string $operator, array $values, bool $isOr = false)
    {
        if (count($columns) !== count($values)) {
            throw new \InvalidArgumentException('The number of columns must match the number of values');
        }
        return $this->pushWhere('RowValues', compact('columns', 'operator', 'values', 'isOr'));
    }

    public function orWhereRowValues(array $columns, string $operator, array $values)
    {
        return $this->whereRowValues($columns, $operator, $values, true);
    }
}
