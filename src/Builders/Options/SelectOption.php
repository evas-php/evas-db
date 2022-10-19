<?php
namespace Evas\Db\Builders\Options;

use Evas\Db\Builders\Options\AbstractOption;
use Evas\Db\Builders\Options\Traits\IsQueryableTrait;

class SelectOption extends AbstractOption
{
    use IsQueryableTrait;

    public $to = 'columns';

    public $type; // ['Raw', 'Column', 'Sub']
    public $sql;
    public $column;
    public $query;
    public $as; // column
    public $bindings;
    // for agregates
    public $function; // ['COUNT', 'SUM', 'MIN', 'MAX', 'AVG']
    // for distinct
    public $distinct = false;

    public static function raw(string $sql, array $bindings = null)
    {
        return new static('Raw', compact('sql', 'bindings'));
    }

    public static function columns($columns = ['*'])
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $options = [];
        foreach ($columns as $as => $column) {
            if (is_string($column) && preg_match('/^\w+(\.\w+)?$/u', $column)) {
                $options[] = static::column($column, $as);
            // } else if (is_string($as) && static::isQueryable($column)) {
            } else {
                $options[] = static::sub($column, $as);
            }
        }
        // return $options;
        return new static('Multiple', compact('options'));
    }

    public static function column(string $column, string $as = null)
    {
        return new static('Column', compact('column', 'as'));
    }

    public static function sub($query, string $as)
    {
        return new static('Sub', compact('query', 'as'));
    }

    public static function distinct(string $column, string $as = null)
    {
        $distinct = true;
        return new static('Column', compact('distinct', 'column', 'as'));
    }

    public static function aggregateOne(string $function, string $column, string $as = null)
    {
        // if (!$as) $as = strtolower($function) . '_' . str_replace('.', '_', $column);
        return new static('Column', compact('function', 'column', 'as'));
    }

    public static function aggregate(string $function, array $columns)
    {
        $options = [];
        foreach ($columns as $as => $column) {
            if (!is_string($as)) $as = null;
            $options[] = static::aggregateOne($function, $column, $as);
        }
        return $options;
    }

    public static function aggregates(array $aggregates)
    {
        $options = [];
        foreach ($aggregates as $function => $columns) {
            if (is_string($columns)) $columns = [$columns];
            $options = array_merge($options, static::aggregate($function, $columns));
        }
        return $options;
    }
}
