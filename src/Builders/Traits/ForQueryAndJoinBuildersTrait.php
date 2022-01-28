<?php
/**
 * Трейт-хелпер для Query и Join сборки.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;


trait ForQueryAndJoinBuildersTrait
{

    // ----------
    // WRAPS
    // ----------

    // protected function wrap(string $value): string
    // {
    //     return $this->db->grammar()->wrap($value);
    // }

    // protected function wrapColumn(string $value): string
    // {
    //     return $this->db->grammar()->wrapColumn($value);
    // }

    // ----------
    // WRAPS
    // ----------

    protected function wrap(string $value): string
    {
        return $this->db->grammar()->wrap($value);
    }

    protected function wrapTable(string $value): string
    {
        return $this->db->grammar()->wrapTable($value);
    }

    // ----------
    // Bindings
    // ----------

    protected function addBinding(string $type, $value)
    {
        $this->bindings[$type][] = $value;
        return $this;
    }

    protected function addBindings(string $type, array $values)
    {
        $this->bindings[$type] = array_merge($this->bindings[$type] ?? [], array_values($values));
        return $this;
    }

    // ----------
    // Value & Operator Helpers
    // ----------

    protected function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } else if ($this->invalidValueAndOperator($value, $operator)) {
            throw new \InvalidArgumentException(json_encode([
                'error' => 'Illegal operator and value combination.',
                'operator' => $operator,
                'value' => $value,
            ]));
        }
        return [$value, $operator];
    }

    protected function invalidValueAndOperator($value, $operator)
    {
        /**
         * @todo Проверять операторы конкретной СУБД
         */
        return is_null($value) && in_array($operator, static::$operators) 
        && !in_array($operator, ['=', '<>', '!=']);
    }

    // ----------
    // FROM
    // ----------

    /**
     * Установка from запросом.
     * @param string $sql
     * @param array|null значения для экранирования
     * @return self
     */
    public function fromRaw(string $sql, array $values = [])
    {
        $this->from = $sql;
        return $this->addBindings('from', $values);
    }

    /**
     * Установка from таблицей или подзапросом.
     * @param string|\Closure|self|OrmQueryBuilder
     * @param string|null псевдоним
     * @return self
     */
    public function from($table, string $as = null)
    {
        if ($this->isQueryable($table)) {
            return $this->fromSub($table, $as);
        }
        $table = $this->wrapTable($table);
        $this->from = $as ? ("$table AS " . $this->wrap($as)) : $table;
        return $this;
    }

    /**
     * Установка from подзапросом.
     * @param \Closure|self|OrmQueryBuilder
     * @param string псевдоним
     * @return self
     */
    public function fromSub($query, string $as)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->fromRaw(
            "($sql) AS " . $this->wrap($as), $bindings
        );
    }
}
