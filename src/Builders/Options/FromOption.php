<?php
namespace Evas\Db\Builders\Options;

use Evas\Db\Builders\Options\AbstractOption;
use Evas\Db\Builders\Options\Traits\IsQueryableTrait;

class FromOption extends AbstractOption
{
    use IsQueryableTrait;

    public $to = 'from';

    public $type; // ['Raw', 'Table', 'Sub']
    public $sql;
    public $table;
    // public $query;
    public $as; // table alias
    public $bindings;

    public static function raw(string $sql, array $bindings = [], string $as = null)
    {
        return new static('Raw', compact('sql', 'bindings'));
    }

    public static function table($table, string $as = null)
    {
        return (static::isQueryable($table) && !is_null($as))
        ? static::sub($table, $as) 
        : new static('Table', compact('table', 'as'));
    }

    public static function sub($query, string $as)
    {
        // return new static('Sub', compact('query', 'as'));
        [$sql, $bindings] = static::createSub($query);
        return static::raw($sql, $bindings, $as);
    }
}
