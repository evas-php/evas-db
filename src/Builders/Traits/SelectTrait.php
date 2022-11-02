<?php
/**
 * Трейт сборки SELECT части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait SelectTrait
{
    /** @var array забираемые поля */
    public $columns = [];

    /** @var array|bool|null кастройка distinct */
    public $distinct;

    /**
     * Добавление запрашиваемых стобцов sql-строкой с экранированием.
     * @param string столбцы sql-строкой
     * @param array|null значения для экранирования
     * @return self
     */
    public function selectRaw(string $sql, array $bindings = [], string $as = null)
    {
        $sql = $this->wrapRoundBrackets($sql);
        if (null !== $as) $sql = "$sql AS " . $this->wrap($as);
        $this->columns[] = $sql;
        return $this->addBindings('columns', $bindings);
    }

    /**
     * Добавление столбца/столбцов.
     * @param mixed столбец/столбцы
     * @return self
     */
    public function select($columns = ['*'])
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        foreach ($columns as $as => $column) {
            if (is_string($as) && ($this->isQueryable($column) || is_string($column))) {
                $this->selectSub($column, $as);
            } else {
                $this->columns[] = $this->wrapRoundBrackets($this->wrap($column));
            }
        }
        return $this;
    }

    /**
     * Добавление подзапроса столбцов.
     * @param string|\Closure|self подзапрос
     * @param string алиас
     */
    public function selectSub($query, string $as)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->selectRaw($sql, $bindings, $as);
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
            $this->distinct = (is_array($columns[0]) || is_bool($columns[0])) 
            ? $columns[0] : $columns;
        } else {
            $this->distinct = true;
        }
        return $this;
    }
}
