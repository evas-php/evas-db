<?php
/**
 * Трейт сборки WHERE части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereTrait
{
    /** @var array WHERE часть */
    public $wheres = [];


    // Help methods

    /**
     * Добавление where условия в сборку.
     * @param string тип where
     * @param array параметры условия
     * @return self
     */
    protected function pushWhere(string $type, array $where)
    {
        $where['type'] = $type;
        $this->wheres[] = $where;
        if (!empty($where['bindings'])) {
            $this->addBindings('wheres', $where['bindings']);
        }
        return $this;
    }


    protected function pushSingleWhere(bool $isOr, $column, $operator = null, $value = null)
    {
        // массив where условий
        if (is_array($column)) {
            return $this->eachSingle('pushSingleWhere', $column, $isOr);
        }

        // Raw запрос через queryable
        if (func_num_args() < 3) return $this->whereNested($column, $isOr);

        // подготовка значения и оператора условия
        $this->prepareValueAndOperator($value, $operator, func_num_args() === 3);

        // IS NULL условие
        if (is_null($value)) return $this->whereNull($column, $isOr, $operator !== '=');

        // подзапрос значения
        if ($this->isQueryable($value)) {
            return $this->pushWhereSub($isOr, $column, $operator, $value);
        }

        // single условие
        $column = $this->prepareColumn($column);
        $bindings = [$value];
        return $this->pushWhere('Single', compact('column', 'operator', 'bindings', 'isOr'));
    }

    /**
     * Добавление where соответствия значения столбца значению другого столбца.
     * @param bool использовать ли OR для склейки
     * @param array|string|\Closure|self столбец или набор соответствий или колбэк или сборщик
     * @param string|mixed|null оператор или второй столбец или null
     * @param mixed|null второй столбец или null
     * @return self
     */
    protected function pushSingleWhereColumn(bool $isOr, $first, $operator = null, $second = null)
    {
        // массив where условий
        if (is_array($first)) {
            return $this->eachSingle('pushSingleWhereColumn', $first, $isOr);
        }

        // подготовка значения и оператора условия
        $this->prepareValueAndOperator($second, $operator, func_num_args() === 3);

        $first = $this->prepareColumn($first);
        $second = $this->prepareColumn($second);
        return $this->pushWhere('SingleColumn', compact('first', 'operator', 'second', 'isOr'));
    }

    protected function eachSingle(string $methodName, array $columns, bool $isOr = false)
    {
        foreach ($columns as $column => $value) {
            if (is_array($value)) {
                $args = array_values($value);
                if (!is_numeric($column)) array_unshift($args, $column);
            } else {
                $args = [$column, '=', $value];
            }
            $this->$methodName($isOr, ...$args);
        }
        return $this;
    }

    /**
     * Добавление OR/AND where Sub.
     */
    protected function pushWhereSub(bool $isOr, $column, $operator, $value = null)
    {
        $column = $this->prepareColumn($column);
        $this->prepareValueAndOperator($value, $operator, func_num_args() === 3);
        [$value, $bindings] = $this->createSub($value);
        return $this->pushWhere('Sub', 
            compact('column', 'operator', 'value', 'bindings', 'isOr')
        );
    }

    /**
     * Подготовка столбца со сборкой подзапроса.
     * @param mixed столбец
     * @param string|null тип экранируемых значений
     * @return string столбец
     */
    protected function prepareColumn($column, string $bindingsType = 'wheres')
    {
        // $column = trim($column, '()');
        [$column, $bindings] = $this->createSub($column);
        if (!empty($bindings)) $this->addBindings($bindingsType, $bindings);
        return $column;
    }


    // ----------
    // Or/And Where Raw
    // ----------

    /**
     * Добавление and where sql-строкой.
     * @param string sql-запрос
     * @param array|null экранируемые значения
     * @param bool|null использовать ли OR для склейки
     * @return self
     */
    public function whereRaw(string $sql, array $bindings = [], bool $isOr = false)
    {
        return $this->pushWhere('Raw', compact('sql', 'bindings', 'isOr'));
    }

    /**
     * Добавление or where sql-строкой.
     * @param string sql-запрос
     * @param array|null экранируемые значения
     * @return self
     */
    public function orWhereRaw(string $sql, array $bindings = [])
    {
        return $this->whereRaw($sql, $bindings, true);
    }


    // ----------
    // Or/And Where
    // ----------

    /**
     * Добавление where AND.
     * @param array|string|\Closure|self соответствия|столбец|подзарос столбца|подзапрос
     * @param string|mixed|null оператор|значение|подзапрос|null
     * @param mixed|null значение|подзапрос|null
     * @return self
     */
    public function where($column, $operator = null, $value = null)
    {
        return $this->pushSingleWhere(false, ...func_get_args());
    }

    /**
     * Добавление where OR.
     * @param array|string|\Closure|self соответствия|столбец|подзарос столбца|подзапрос
     * @param string|mixed|null оператор|значение|подзапрос|null
     * @param mixed|null значение|подзапрос|null
     * @return self
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->pushSingleWhere(true, ...func_get_args());
    }


    // ----------
    // Or/And Where Column
    // ----------

    /**
     * Добавление where AND соответствия значений столбцов.
     * @param array|string соответствия или столбец
     * @param string|mixed|null оператор или второй столбец или null
     * @param mixed|null второй столбец или null
     * @return self
     */
    public function whereColumn($first, $operator = null, $second = null)
    {
        return $this->pushSingleWhereColumn(true, ...func_get_args());
    }

    /**
     * Добавление where OR соответствия значений столбцов.
     * @param array|string соответствия или столбец
     * @param string|mixed|null оператор или второй столбец или null
     * @param mixed|null второй столбец или null
     * @return self
     */
    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->pushSingleWhereColumn(false, ...func_get_args());
    }


    // ----------
    // Or/And Where Sub
    // ----------

    public function whereSub($column, $operator, $value = null)
    {
        return $this->pushWhereSub(false, ...func_get_args());
    }

    public function orWhereSub($column, $operator, $value = null)
    {
        return $this->pushWhereSub(true, ...func_get_args());
    }


    // ----------
    // Or/And Nested where
    // ----------

    public function whereNested($query, bool $isOr = false)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->pushWhere('Nested', compact('sql', 'bindings', 'isOr'));
    }

    public function orWhereNested($query)
    {
        return $this->whereNested($query, true);
    }


    // ----------
    // Or/And Where Is (Not) Null
    // ----------

    /**
     * Добавление where AND IS NULL.
     * @param array|string\Closure|self стобцы или столбец или подзапрос столбца
     * @param bool|null использовать ли OR для склейки
     * @param bool|null использовать ли NOT
     * @return self
     */
    public function whereNull($column, bool $isOr = false, bool $isNot = false)
    {
        if (is_array($column)) {
            foreach ($column as $sub) {
                $this->whereNull($sub, $isOr, $isNot);
            }
            return $this;
        }
        $column = $this->prepareColumn($column);
        return $this->pushWhere('Null', compact('column', 'isOr', 'isNot'));
    }

    /**
     * Добавление where OR IS NULL.
     * @param array|string стобцы или столбец
     * @return self
     */
    public function orWhereNull($column)
    {
        return $this->whereNull($column, true);
    }

    /**
     * Добавление where AND IS NOT NULL.
     * @param array|string стобцы или столбец
     * @return self
     */
    public function whereNotNull($column)
    {
        return $this->whereNull($column, false, true);
    }

    /**
     * Добавление where OR IS NOT NULL.
     * @param array|string стобцы или столбец
     * @return self
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNull($column, true, true);
    }


    // ----------
    // Or/And Where (Not) In
    // ----------

    /**
     * Добавление where AND/OR (NOT) IN.
     * @param string|\Closure|self столбец или подзарос столбца
     * @param array|\Closure|self массив значений или подзапрос
     * @param bool|null использовать ли OR для склейки
     * @param bool|null использовать ли NOT
     * @return self
     */
    public function whereIn($column, $values, bool $isOr = false, bool $isNot = false)
    {
        $column = $this->prepareColumn($column);
        if (is_array($values)) {
            $bindings = $values;
        } else {
            [$values, $bindings] = $this->createSub($values);
        }
        return $this->pushWhere('In', compact('column', 'values', 'bindings', 'isOr', 'isNot'));
    }

    /**
     * Добавление where OR IN.
     * @param string|\Closure|self столбец или подзарос столбца
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, true);
    }

    /**
     * Добавление where AND NOT IN.
     * @param string|\Closure|self столбец или подзарос столбца
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function whereNotIn($column, $values)
    {
        return $this->whereIn($column, $values,false, true);
    }

    /**
     * Добавление where OR NOT IN.
     * @param string|\Closure|self столбец или подзарос столбца
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereIn($column, $values, true, true);
    }
}
