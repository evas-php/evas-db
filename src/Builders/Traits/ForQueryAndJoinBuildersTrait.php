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

    /**
     * Пробрасываем обёртку имени таблицы из грамматики.
     * @param string имя таблицы
     * @return string обёрнутое имя таблицы
     */
    protected function wrapTable(string $value): string
    {
        return $this->db->grammar()->wrapTable($value);
    }

    // ----------
    // Bindings
    // ----------

    /**
     * Добавление экранируемого значения.
     * @param string назначение значения
     * @param mixed значение
     * @return self
     */
    protected function addBinding(string $type, $value)
    {
        $this->bindings[$type][] = $value;
        return $this;
    }

    /**
     * Добавление экранируемых значений.
     * @param string назначение значения
     * @param array массив значений
     * @return self
     */
    protected function addBindings(string $type, array $values)
    {
        $this->bindings[$type] = array_merge($this->bindings[$type] ?? [], array_values($values));
        return $this;
    }

    // ----------
    // Value & Operator Helpers
    // ----------

    /**
     * Подготовка значения и оператора.
     * Для методов, в которых можно опутить оператор сравения - "=".
     * @param mixed значение
     * @param mixed оператор
     * @param bool|null использовать ли оператор в качестве значения
     * @return array [значение, оператор]
     * @throws \InvalidArgumentException
     */
    protected function prepareValueAndOperator($value, $operator, bool $useDefault = false): array
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

    /**
     * Проверка на неправильность значения и оператора.
     * @param mixed значение
     * @param mixed оператор
     * @return bool
     */
    protected function invalidValueAndOperator($value, $operator): bool
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
     * Установка from sql-сторокой.
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
     * Установка from таблицей или sql-подзапросом.
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
        $this->from = $as ? ("$table AS " . $this->wrapTable($as)) : $table;
        return $this;
    }

    /**
     * Установка from sql-подзапросом.
     * @param \Closure|self|OrmQueryBuilder
     * @param string псевдоним
     * @return self
     */
    public function fromSub($query, string $as)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->fromRaw(
            "($sql) AS " . $this->wrapTable($as), $bindings
        );
    }
}
