<?php
namespace Evas\Db\Builders\Options;

use Evas\Db\Builders\Options\AbstractOption;

class GroupByOption extends AbstractOption
{
    public $to = 'groups';

    public $type; // ['Raw', 'Column']
    public $sql;
    public $column;
    public $function;

    public static function raw(string $sql, array $bindings = [])
    {
        return new static('Raw', compact('sql', 'bindings'));
    }

    public static function column(string $column, string $function = null)
    {
        return new static('Column', compact('column', 'function'));
    }

    public static function columns(array $columns)
    {
        return array_map([static::class, 'column'], $columns);
    }
}
