<?php
/**
 * Трейт сборки агрегатов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait AggregatesTrait
{
    // ----------
    // Добавление получения агрегации в запрос
    // ----------

    /**
     * Добавление агрегаций в запрос.
     * @param array агрегаты
     * @return self
     */
    public function aggregates(array $aggregates)
    {
        foreach ($aggregates as $function => $columns) {
            if (is_string($columns)) $columns = [$columns];
            $this->aggregate($function, $columns);
        }
        return $this;
    }

    /**
     * Добавление агрегации в запрос.
     * @param string функция агрегирования (COUNT,SUM,MIN,MAX,AVG)
     * @param array столбцы агрегации
     * @return self
     */
    public function aggregate(string $function, array $columns = ['*'])
    {
        $select = [];
        foreach ($columns as $column) {
            $as = strtolower($function) . "_{$column}";
            $select[$as] = strtoupper($function) . "({$this->wrapColumn($column)})";
        }
        $this->addSelect($select);
        return $this;
    }

    /**
     * Агрегация количества значений в столбце.
     * @param string столбец
     * @return self
     */
    public function count(string ...$columns)
    {
        return $this->aggregate('count', $columns);
    }

    /**
     * Агрегация суммы значений в столбце.
     * @param string столбец
     * @return self
     */
    public function sum(string ...$columns)
    {
        return $this->aggregate('sum', $columns);
    }

    /**
     * Агрегация минимального значения в столбце.
     * @param string столбец
     * @return self
     */
    public function min(string ...$columns)
    {
        return $this->aggregate('min', $columns);
    }

    /**
     * Агрегация максимального значения в столбце.
     * @param string столбец
     * @return self
     */
    public function max(string ...$columns)
    {
        return $this->aggregate('max', $columns);
    }

    /**
     * Агрегация среднего значения в столбце.
     * @param string столбец
     * @return self
     */
    public function avg(string ...$columns)
    {
        return $this->aggregate('avg', $columns);
    }

    // ----------
    // Добавление агрегации с отправкой запроса.
    // ----------

    /**
     * Получение агрегаций.
     * @param array агрегаты
     * @return self
     */
    public function getAggregates(array $aggregates)
    {
        return $this->aggregates($aggregates)->get();
    }

    /**
     * Получение агрегации.
     * @param array агрегаты
     * @return self
     */
    public function getAggregate(string $function, array $columns = ['*'])
    {
        return $this->aggregate($function, $columns)->get();
    }


    /**
     * Получение агрегации количества значений в столбце.
     * @param string столбец
     * @return self
     */
    public function getCount(string ...$columns)
    {
        return $this->getAggregate('count', $columns);
    }

    /**
     * Получение агрегации суммы значений в столбце.
     * @param string столбец
     * @return self
     */
    public function getSum(string ...$columns)
    {
        return $this->getAggregate('sum', $columns);
    }

    /**
     * Получение агрегации минимального значения в столбце.
     * @param string столбец
     * @return self
     */
    public function getMin(string ...$columns)
    {
        return $this->getAggregate('min', $columns);
    }

    /**
     * Получение агрегации максимального значения в столбце.
     * @param string столбец
     * @return self
     */
    public function getMax(string ...$columns)
    {
        return $this->getAggregate('max', $columns);
    }

    /**
     * Получение агрегации среднего значения в столбце.
     * @param string столбец
     * @return self
     */
    public function getAvg(string ...$columns)
    {
        return $this->getAggregate('avg', $columns);
    }
}
