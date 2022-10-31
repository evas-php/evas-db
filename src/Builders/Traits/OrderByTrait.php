<?php
/**
 * Трейт сборки ORDER BY части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

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
        $this->orders[] = $sql;
        // if ($values) $this->addBindings('orders', $values);
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
        if (is_string($column)) {
            $this->orders[] = [$this->wrap($column), $isDesc];
        } else if ($this->isQueryable($column)) {
            [$sql, $bindings] = $this->createSub($column);
            $this->orders[] = [$sql, $isDesc];
            // $this->addBindings('orders', $bindings);
        } else if (is_array($column)) {
            foreach ($column as $col => $subDesc) {
                if (is_numeric($col) && is_string($subDesc)) {
                    $col = $subDesc;
                    $subDesc = $isDesc;
                }
                $this->orderBy($col, $subDesc);
            }
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an array, a string or a queryable, %s given',
                __METHOD__, gettype($id)
            ));
        }
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
