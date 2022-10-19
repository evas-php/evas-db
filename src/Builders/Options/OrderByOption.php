<?php
namespace Evas\Db\Builders\Options;

use Evas\Db\Builders\Options\AbstractOption;

class OrderByOption extends AbstractOption
{
    public $to = 'orders';

    public $type; // ['Raw', 'Column']
    public $sql;
    public $column;
    public $isDesc;

    public static function raw(string $sql, bool $isDesc = false)
    {
        return new static('Raw', compact('sql', 'isDesc'));
    }

    public static function column(string $column, bool $isDesc = false)
    {
        return new static('Column', compact('column', 'isDesc'));
    }

    public static function columns($columns, bool $isDesc = false)
    {
        $orders = [];
        if (is_array($columns)) {
            foreach ($columns as $column => $subDesc) {
                if (is_numeric($column) && is_string($subDesc)) {
                    $column = $subDesc;
                    $subDesc = $isDesc;
                }
                $orders[] = static::column($column, $subDesc);
            }
        } else if (is_string($columns)) {
            $orders[] = static::column($columns, $subDesc);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an array, a string or a Queryable, %s given',
                __METHOD__, gettype($id)
            ));
        }
        return $orders;
    }
}
