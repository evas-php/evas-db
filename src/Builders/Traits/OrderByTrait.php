<?php
/**
 * Трейт сборки ORDER BY части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\OrderByOption;

trait OrderByTrait
{
    /** @var array поля сортировки */
    public $orders = [];
    
    /**
     * Установка сортировки sql-строкой.
     * @param string sql
     * @return self
     */
    public function orderByRaw(string $sql)
    {
        $this->orders[] = OrderByOption::raw($sql);
        return $this;
    }

    /**
     * Установка сортировки.
     * @param array|string|\Closure|self столбцы
     * @param bool|null сортировать по убыванию
     * @return self
     * @throws \InvalidArgumentException
     */
    public function orderBy($column, bool $isDesc = false)
    {
        $this->orders = array_merge($this->orders, OrderByOption::columns($column, $isDesc));
        return $this;
    }

    /**
     * Установка сортировки по убыванию.
     * @param array|string|\Closure|self столбцы
     * @return self
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, true);
    }
}
