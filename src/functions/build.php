<?php
// namespace Evas\Db;

// use Evas\Db\Builders\Options\AbstractOption;
// use Evas\Db\Builders\Options\SelectOption;
// use Evas\Db\Builders\Options\FromOption;
// use Evas\Db\Builders\Options\WhereOption;
// use Evas\Db\Builders\Options\GroupByOption;
// use Evas\Db\Builders\Options\HavingOption;
// use Evas\Db\Builders\Options\OrderByOption;

// use Evas\Db\Builders\Options\LimitOption;
// use Evas\Db\Builders\Options\UnionOption;

class Option
{
    public $method;
    public $args;

    public function __construct(string $method, array $args)
    {
        $this->method = $method;
        $this->args = $args;
    }
}

use Evas\Db\Builders\QueryBuilder;
use Evas\Db\Interfaces\DatabaseInterface;

function build(DatabaseInterface $db, Option ...$options) {
    $builder = new QueryBuilder($db);
    foreach ($options as &$option) {
        // $to = $option->to;
        // if (empty($to)) {
        //     throw new \RuntimeException('Query option not has $to property');
        // }
        // $builder->$to[] = $option;
        $method = $option->method;
        $builder->$method(...$option->args);
    }
    return $builder;
}

function selectRaw(string $sql, array $bindings = null) {
    // return SelectOption::raw($sql, $bindings);
    return new Option('selectRaw', func_get_args());
}

function select($columns = ['*']) {
    // return SelectOption::columns(func_get_args());
    return new Option('select', func_get_args());
}

function selectSub($query, string $as) {
    // return SelectOption::sub($query, $as);
    return new Option('selectSub', func_get_args());
}

function fromRaw(string $sql, array $bindings = []) {
    // return FromOption::raw($sql, $bindings);
    return new Option('fromRaw', func_get_args());
}

function from($table, string $as = null) {
    // return FromOption::table($table, $as);
    return new Option('from', func_get_args());
}

function fromSub($query, string $as) {
    // return FromOption::sub($query, $as);
    return new Option('fromSub', func_get_args());
}

