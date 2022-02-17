<?php
/**
 * Трейт добавления запрашиваемых стобцов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait SelectTrait
{
    // ----------
    // SELECT COLUMNS
    // ----------

    /**
     * Установка запрашиваемых стобцов sql-строкой с экранированием.
     * @param string столбцы sql-строкой
     * @param array|null значения для экранирования
     * @return self
     */
    public function selectRaw($sql, array $values = null)
    {
        $this->columns = [$sql];
        $this->bindings['select'] = [];
        if ($values) $this->addBindings('select', $values);
        return $this;
    }

    /**
     * Добавление запрашиваемых стобцов sql-строкой с экранированием.
     * @param string столбцы sql-строкой
     * @param array|null значения для экранирования
     * @return self
     */
    public function addSelectRaw($sql, array $values = null)
    {
        $this->columns[] = $sql;
        if ($values) $this->addBindings('select', $values);
        return $this;
    }

    /**
     * Установка запрашиваемых столбцов с затиранием старых.
     * @param mixed|null столбцы
     * @return self
     */
    public function select($columns = null)
    {
        if (empty($columns)) $columns = ['*'];
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
     * @param string|\Closure|self
     * @param string алиас
     */
    public function selectSub($query, string $as)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->addSelectRaw(
            "$sql AS " . $this->wrapColumn($as), $bindings
        );
    }


    // ----------
    // DISTINCT
    // ----------

    /**
     * Установка distinct.
     * @param array|string|bool|null columns|column|all for distinct
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
