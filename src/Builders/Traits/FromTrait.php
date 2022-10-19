<?php
/**
 * Трейт сборки FROM части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\FromOption;

trait FromTrait
{
    /** @var array FROM часть */
    public $from = [];

    /**
     * Установка from sql сторокой.
     * @param string sql строка
     * @param array|null значения для экранирования
     * @return self
     */
    public function fromRaw(string $sql, array $bindings = [])
    {
        $this->from[] = FromOption::raw($sql, $bindings);
        return $this;
        // return $this->addBindings('from', $bindings);
    }

    /**
     * Установка from таблицей или sql-подзапросом.
     * @param string|\Closure|self
     * @param string|null псевдоним
     * @return self
     */
    public function from($table, string $as = null)
    {
        // if ($this->isQueryable($table) && !is_null($as)) {
        //     return $this->fromSub($table, $as);
        // }
        // // $table = $this->wrap($table);
        // // $this->from = $as ? ("$table AS " . $this->wrapOne($as)) : $table;
        // $this->from = $this->wrap($as ? "{$table} AS {$as}" : $table);
        $this->from[] = FromOption::table($table, $as);
        return $this;
    }

    /**
     * Установка from sql-подзапросом.
     * @param \Closure|self
     * @param string псевдоним
     * @return self
     */
    public function fromSub($query, string $as)
    {
        // [$sql, $bindings] = $this->createSub($query);
        // return $this->fromRaw("{$sql} AS " . $this->wrapOne($as), $bindings);
        $this->from[] = FromOption::sub($query, $as);
        return $this;
    }
}
