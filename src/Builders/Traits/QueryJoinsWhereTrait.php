<?php
/**
 * Трейт добавления joins с WHERE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\JoinBuilder;

trait QueryJoinsWhereTrait
{
    // ----------
    // JOIN Helpers
    // ----------

    /**
     * Установка JOIN WHERE.
     * @param string тип join
     * @param string таблица
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    protected function setJoinWhere(string $type, string $table, $first, string $operator = null, string $second = null)
    {
        $condition = func_get_args();
        return $this->realSetJoin(array_shift($condition), [array_shift($condition)], $condition, true);
    }

    /**
     * Установка JOIN WHERE с подзапросом таблицы.
     * @param string тип join
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    protected function setJoinSubWhere(string $type, $query, string $as, $first, string $operator = null, string $second = null)
    {
        $condition = func_get_args();
        return $this->realSetJoin(array_shift($condition), [
            array_shift($condition), array_shift($condition)
        ], $condition, true);
    }

    // ----------
    // INNER JOIN
    // ----------

    /**
     * Добавление INNER JOIN WHERE.
     * @param string таблица
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function joinWhere(string $table, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinWhere('INNER', ...func_get_args());
    }

    /**
     * Добавление INNER JOIN WHERE с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function joinSubWhere($query, string $as, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinSubWhere('INNER', ...func_get_args());
    }
}
