<?php
namespace Evas\Db\Builders\Traits;

trait SelectTrait
{
    // ----------
    // SELECT COLUMNS
    // ----------

    /**
     * Установка запрашиваемых стобцов с экранированием.
     * @param string|\Closure|self|OrmQueryBuilder|array
     * @param array|null значения для экранирования
     * @return self
     */
    public function selectRaw($sql, array $values = null)
    {
        // $this->addSelect($sql);
        $this->columns = [$sql];
        $this->bindings['select'] = [];
        if ($values) $this->addBindings('select', $values);
        return $this;
    }

    /**
     * Добавление запрашиваемых стобцов с экранированием.
     * @param string|\Closure|self|OrmQueryBuilder|array
     * @param array|null значения для экранирования
     * @return self
     */
    public function addSelectRaw($sql, array $values = null)
    {
        // $this->addSelect($sql);
        $this->columns[] = $sql;
        if ($values) $this->addBindings('select', $values);
        return $this;
    }

    /**
     * Установка запрашиваемых столбцов с затиранием старых.
     * @param mixed столбцы
     * @return self
     */
    public function select($columns = ['*'])
    {
        $this->columns = [];
        $this->bindings['select'] = [];
        return $this->addSelect(...func_get_args());
    }

    /**
     * Добавление запрашиваемых столбцов.
     * @param mixed столбцы
     * @return self
     */
    public function addSelect($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        foreach ($columns as $as => $column) {
            if (is_string($as) && ($this->isQueryable($column) || is_string($column))) {
                // if (is_null($this->columns)) {
                //     $this->select($this->from.'.*');
                // }
                $this->selectSub($column, $as);
            } else {
                $this->columns[] = $this->wrapColumn($column);
                // $this->columns[] = $column;
            }
        }
        return $this;
    }

    /**
     * Подзапрос запрашиваемых столбцов.
     * @param string|\Closure|self|OrmQueryBuilder
     * @param string алиас
     */
    public function selectSub($query, string $as)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->addSelectRaw(
            "$sql AS " . $this->wrap($as), $bindings
        );
    }


    // ----------
    // DISTINCT
    // ----------

    /**
     * Установка distinct.
     * @param array|string|bool columns|column|all for distinct
     * @return self
     */
    public function distinct()
    {
        $columns = func_get_args();
        if (count($columns) > 0) {
            $this->distinct = is_array($columns[0]) || is_bool($columns[0]) ? $columns[0] : $columns;
        } else {
            $this->distinct = true;
        }
        return $this;
    }
}
