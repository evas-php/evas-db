<?php
/**
 * Трейт сборки SELECT части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\SelectOption;

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
    public function selectRaw(string $sql, array $bindings = null)
    {
        $this->columns[] = SelectOption::raw($sql, $bindings);
        return $this;
    }

    /**
     * Добавление запрашиваемых столбцов.
     * @param mixed столбцы
     * @return self
     */
    public function select($columns = ['*'])
    {
        // $this->columns = array_merge($this->columns, SelectOption::columns(...func_get_args()));
        $this->columns = SelectOption::columns(...func_get_args());
        return $this;
    }

    /**
     * Подзапрос запрашиваемых столбцов.
     * @param string|\Closure|self
     * @param string алиас
     */
    public function selectSub($query, string $as)
    {
        $this->columns[] = SelectOption::sub($query, $as);
        return $this;
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
