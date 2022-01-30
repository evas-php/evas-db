<?php
/**
 * Трейт добавления joins с USING.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait QueryJoinsUsingTrait
{
    // ----------
    // INNER JOIN
    // ----------

    /**
     * Добавление INNER JOIN USING.
     * @param string таблица
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function joinUsing(string $table, string $column, string $type = 'INNER')
    {
        return $this->setJoin($type, $table, $column);
    }

    /**
     * Добавление INNER JOIN USING с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function joinSubUsing($query, string $as, string $column, string $type = 'INNER')
    {
        return $this->setJoin($type, $query, $as, $column);
    }

    // ----------
    // LEFT JOIN
    // ----------

    /**
     * Добавление LEFT JOIN USING.
     * @param string таблица
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function leftJoinUsing(string $table, string $column)
    {
        return $this->joinUsing($table, $column, 'LEFT');
    }

    /**
     * Добавление LEFT JOIN USING с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function leftJoinSubUsing($query, string $as, string $column)
    {
        return $this->joinSubUsing($query, $as, $column, 'LEFT');
    }

    // ----------
    // LEFT OUTER JOIN
    // ----------

    /**
     * Добавление LEFT OUTER JOIN USING.
     * @param string таблица
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function leftOuterJoinUsing(string $table, string $column)
    {
        return $this->joinUsing($table, $column, 'LEFT OUTER');
    }

    /**
     * Добавление LEFT OUTER JOIN USING с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function leftOuterJoinSubUsing($query, string $as, string $column)
    {
        return $this->joinSubUsing($query, $as, $column, 'LEFT OUTER');
    }

    // ----------
    // RIGHT JOIN
    // ----------

    /**
     * Добавление RIGHT JOIN USING.
     * @param string таблица
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function rightJoinUsing(string $table, string $column)
    {
        return $this->joinUsing($table, $column, 'RIGHT');
    }

    /**
     * Добавление RIGHT JOIN USING с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function rightJoinSubUsing($query, string $as, string $column)
    {
        return $this->joinSubUsing($query, $as, $column, 'RIGHT');
    }

    // ----------
    // RIGHT OUTER JOIN
    // ----------

    /**
     * Добавление RIGHT OUTER JOIN USING.
     * @param string таблица
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function rightOuterJoinUsing(string $table, string $column)
    {
        return $this->joinUsing($table, $column, 'RIGHT OUTER');
    }

    /**
     * Добавление RIGHT OUTER JOIN USING с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string столбец соответствия
     * @param string тип join
     * @return self
     */
    public function rightOuterJoinSubUsing($query, string $as, string $column)
    {
        return $this->joinSubUsing($query, $as, $column, 'RIGHT OUTER');
    }
}
