<?php
/**
 * Трейт добавления joins с WHERE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait JoinsWhereTrait
{
    // Help methods

    /**
     * Установка JOIN WHERE.
     * @param string тип join
     * @param string таблица
     * @param string|\Closure первый столбец или колбек
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    protected function setJoinWhere(
        string $type, string $table, $first, string $operator = null, string $second = null
    ) {
        return $this->realSetJoin($type, [$table], array_slice(func_get_args(), 2), true);
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
    protected function setJoinSubWhere(
        string $type, $query, string $as, $first, string $operator = null, string $second = null
    ) {
        return $this->realSetJoin($type, [$query, $as], array_slice(func_get_args(), 3), true);
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
    public function joinWhere(
        string $table, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoinWhere('INNER', ...func_get_args());
    }

    /**
     * Добавление INNER JOIN WHERE с подзапросом таблицы.
     * @param string|\Closure|self подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function joinSubWhere(
        $query, string $as, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoinSubWhere('INNER', ...func_get_args());
    }
}
