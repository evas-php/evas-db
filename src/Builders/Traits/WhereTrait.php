<?php
namespace Evas\Db\Builders\Traits;

trait WhereTrait
{
    // Where helpers

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
            // [$sub, $bindings] = $this->createSub($column);
            // return $this->addBinding($bindings, 'where')
            //     ->where(new Expression('('.$sub.')'), $operator, $value, $boolean);

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

    public function whereNested(\Closure $callback, bool $isOr = false)
    {
        call_user_func($callback, $query = $this->forNestedWhere());
        return $this->addNestedWhereQuery($query, $isOr);
    }

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


    // Or/And Where Raw

    public function whereRaw(string $sql, array $values = [], bool $isOr = false)
    {
        return $this->pushWhere('Raw', compact('sql', 'values', 'isOr'));
    }

    public function orWhereRaw(string $sql, array $values = [])
    {
        return $this->whereRaw($sql, $values, true);
    }

    // Or/And Where

    public function where($column, $operator = null, $value = null)
    {
        return $this->addSingleWhere(false, ...func_get_args());
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->addSingleWhere(true, ...func_get_args());
    }

    // Or/And Where Column

    public function whereColumn($first, $operator = null, $second = null)
    {
        return $this->addSingleWhereColumn(false, ...func_get_args());
    }

    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->addSingleWhereColumn(true, ...func_get_args());
    }


    // Or/And Where (Not) Null

    public function whereNull($columns, bool $isOr = false, bool $isNot = false)
    {
        if (is_array($columns)) {
            $query = $this->whereNested(function ($query) use ($columns, $isNot) {
                foreach ($columns as $column) {
                    $query->whereNull($column, false, $isNot);
                }
            }, $isOr);
        } else {
            $column = $columns;
            $this->pushWhere('Null', compact('column', 'isOr', 'isNot'));
        }
        return $this;
    }

    public function orWhereNull($columns)
    {
        return $this->whereNull($columns, true);
    }

    public function whereNotNull($columns)
    {
        return $this->whereNull($columns, false, true);
    }

    public function orWhereNotNull($columns)
    {
        return $this->whereNull($columns, true, true);
    }

    // Or/And Where (Not) In

    public function whereIn(string $column, $values, bool $isOr = false, bool $isNot = false)
    {
        if ($this->isQueryable($values)) {
            return $this->addSingleWhere($isOr, $column, 'in', $values);
        }
        return $this->pushWhere('In', compact('column', 'values', 'isOr', 'isNot'));
    }

    public function orWhereIn(string $column, $values)
    {
        return $this->whereIn($column, $values, true);
    }

    public function whereNotIn(string $column, $values)
    {
        return $this->whereIn($column, $values, false, true);
    }

    public function orWhereNotIn(string $column, $values)
    {
        return $this->whereIn($column, $values, true, true);
    }
}
