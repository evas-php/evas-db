<?php
/**
 * Трейт сборки базовых where.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereTrait
{
    // ----------
    // Where helpers
    // ----------

    /**
     * Добавление where соответствия значения столбца переданному значению.
     * @param bool использовать ли OR для склейки
     * @param array|string|\Closure|self столбец или набор соответствий или колбэк или сборщик
     * @param string|mixed|null оператор или значение или null
     * @param mixed|null значение или null
     * @return self
     */
    protected function addSingleWhere(bool $isOr, $column, $operator = null, $value = null)
    {
        // массив условий
        if (is_array($column)) {
            foreach ($column as $col => $val) {
                if (is_array($val)) {
                    $vals = array_values($val);
                    if (!is_numeric($col)) array_unshift($vals, $col);
                } else {
                    $vals = [$col, '=', $val];
                }
                $this->addSingleWhere($isOr, ...$vals);
            }
            return $this;
        }

        // подзапрос столбца
        if ($column instanceof \Closure) {
            return $this->whereNested($column, $isOr);
        }

        // подготовка значения и оператора условия
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 3
        );

        /**
         * @todo вложенным подзапрос столбца через QueryBuilder
         * @todo вложенным подзапрос значения через QueryBuilder
         */
        if ($this->isQueryable($column) && ! is_null($operator)) {
            // [$sql, $bindings] = $this->createSub($column);
            // return $this->addBinding($bindings, 'where')
            //     ->where($sql, $operator, $value, $boolean);

        }

        // IS NULL условие
        if (is_null($value)) {
            return $this->whereNull($column, $isOr, $operator !== '=');
        }

        // подзапрос значения
        if ($value instanceof \Closure) {
            return $this->whereSub($column, $operator, $value, $isOr);
        }

        // single условие
        return $this->pushWhere('Single', compact('column', 'operator', 'value', 'isOr'));
    }

    /**
     * Добавление where соответствия значения столбца значению другого столбца.
     * @param bool использовать ли OR для склейки
     * @param array|string|\Closure|self столбец или набор соответствий или колбэк или сборщик
     * @param string|mixed|null оператор или второй столбец или null
     * @param mixed|null второй столбец или null
     * @return self
     */
    protected function addSingleWhereColumn(bool $isOr, $first, $operator = null, $second = null)
    {
        // массив условий
        if (is_array($first)) {
            foreach ($first as $col => $val) {
                if (is_array($val)) {
                    $vals = array_values($val);
                    if (!is_numeric($col)) array_unshift($vals, $col);
                } else {
                    $vals = [$col, '=', $val];
                }
                $this->addSingleWhereColumn($isOr, ...$vals);
            }
            return $this;
        }

        // подготовка значения и оператора условия
        [$second, $operator] = $this->prepareValueAndOperator(
            $second, $operator, func_num_args() === 3
        );

        return $this->pushWhere('SingleColumn', compact('first', 'operator', 'second', 'isOr'));
    }

    /**
     * Добавление вложенного условия через колбек.
     * @param \Closure колбек
     * @param bool|null использовать ли OR для склейки
     * @return self
     */
    public function whereNested(\Closure $callback, bool $isOr = false)
    {
        call_user_func($callback, $query = $this->forNestedWhere());
        return $this->addNestedWhereQuery($query, $isOr);
    }

    /**
     * Добавление вложенного условия через сборщик.
     * @param self сборщик
     * @param bool|null использовать ли OR для склейки
     * @return self
     */
    public function addNestedWhereQuery(self $query, bool $isOr = false)
    {
        if (count($query->wheres)) {
            $sql = $query->buildWheres();
            $values = $query->getBindings('where');
            $this->pushWhere('Nested', compact('sql', 'values', 'isOr'));
        }
        return $this;
    }

    /**
     * Подзапрос значения условия запроса полноценным SELECT-запросом.
     * @param string столбец
     * @param string оператор
     * @param \Closure колбэк
     * @param bool оператор разделения условий
     * @return self
     */
    protected function whereSub(string $column, string $operator, \Closure $callback, bool $isOr)
    {
        call_user_func($callback, $query = $this->forSubQuery());
        [$sql, $values] = $query->getSqlAndBindings();
        return $this->pushWhere('Sub', compact('column', 'operator', 'sql', 'values', 'isOr'));
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
    public function whereRaw(string $sql, array $values = [], bool $isOr = false)
    {
        return $this->pushWhere('Raw', compact('sql', 'values', 'isOr'));
    }

    /**
     * Добавление or where sql-строкой.
     * @param string sql-запрос
     * @param array|null экранируемые значения
     * @return self
     */
    public function orWhereRaw(string $sql, array $values = [])
    {
        return $this->whereRaw($sql, $values, true);
    }

    // ----------
    // Or/And Where
    // ----------

    /**
     * Добавление and where соответствия значения столбца переданному значению.
     * @param array|string|\Closure|self столбец или набор соответствий или колбэк или сборщик
     * @param string|mixed|null оператор или значение или null
     * @param mixed|null значение или null
     * @return self
     */
    public function where($column, $operator = null, $value = null)
    {
        return $this->addSingleWhere(false, ...func_get_args());
    }

    /**
     * Добавление or where соответствия значения столбца переданному значению.
     * @param array|string|\Closure|self столбец или набор соответствий или колбэк или сборщик
     * @param string|mixed|null оператор или значение или null
     * @param mixed|null значение или null
     * @return self
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->addSingleWhere(true, ...func_get_args());
    }

    // ----------
    // Or/And Where Column
    // ----------

    /**
     * Добавление and where соответствия значения столбца значению другого столбца.
     * @param array|string|\Closure|self столбец или набор соответствий или колбэк или сборщик
     * @param string|mixed|null оператор или второй столбец или null
     * @param mixed|null второй столбец или null
     * @return self
     */
    public function whereColumn($first, $operator = null, $second = null)
    {
        return $this->addSingleWhereColumn(false, ...func_get_args());
    }

    /**
     * Добавление or where соответствия значения столбца значению другого столбца.
     * @param array|string|\Closure|self столбец или набор соответствий или колбэк или сборщик
     * @param string|mixed|null оператор или второй столбец или null
     * @param mixed|null второй столбец или null
     * @return self
     */
    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->addSingleWhereColumn(true, ...func_get_args());
    }


    // ----------
    // Or/And Where (Not) Null
    // ----------

    /**
     * Добавление where IS NULL через AND.
     * @param array|string стобцы или столбец
     * @param bool|null использовать ли OR для склейки
     * @param bool|null использовать ли NOT
     * @return self
     * @throws \InvalidArgumentException
     */
    public function whereNull($columns, bool $isOr = false, bool $isNot = false)
    {
        if (is_array($columns)) {
            $query = $this->whereNested(function ($query) use ($columns, $isNot) {
                foreach ($columns as $column) {
                    $query->whereNull($column, false, $isNot);
                }
            }, $isOr);
        } else if (is_string($columns)) {
            $column = $columns;
            $this->pushWhere('Null', compact('column', 'isOr', 'isNot'));
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an array or a string, %s given',
                __METHOD__, gettype($columns)
            ));
        }
        return $this;
    }

    /**
     * Добавление where IS NULL через OR.
     * @param array|string стобцы или столбец
     * @return self
     */
    public function orWhereNull($columns)
    {
        return $this->whereNull($columns, true);
    }

    /**
     * Добавление where IS NOT NULL через AND.
     * @param array|string стобцы или столбец
     * @return self
     */
    public function whereNotNull($columns)
    {
        return $this->whereNull($columns, false, true);
    }

    /**
     * Добавление where IS NOT NULL через OR.
     * @param array|string стобцы или столбец
     * @return self
     */
    public function orWhereNotNull($columns)
    {
        return $this->whereNull($columns, true, true);
    }


    // ----------
    // Or/And Where (Not) In
    // ----------

    /**
     * Добавление where IN через AND.
     * @param string столбец
     * @param array|\Closure|self массив значений или подзапрос
     * @param bool|null использовать ли OR для склейки
     * @param bool|null использовать ли NOT
     * @return self
     */
    public function whereIn(string $column, $values, bool $isOr = false, bool $isNot = false)
    {
        if ($this->isQueryable($values)) {
            return $this->addSingleWhere($isOr, $column, 'in', $values);
        }
        return $this->pushWhere('In', compact('column', 'values', 'isOr', 'isNot'));
    }

    /**
     * Добавление where IN через OR.
     * @param string столбец
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function orWhereIn(string $column, $values)
    {
        return $this->whereIn($column, $values, true);
    }

    /**
     * Добавление where NOT IN через AND.
     * @param string столбец
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function whereNotIn(string $column, $values)
    {
        return $this->whereIn($column, $values, false, true);
    }

    /**
     * Добавление where NOT IN через OR.
     * @param string столбец
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function orWhereNotIn(string $column, $values)
    {
        return $this->whereIn($column, $values, true, true);
    }
}
