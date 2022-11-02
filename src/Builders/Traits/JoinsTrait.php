<?php
/**
 * Трейт добавления joins.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\JoinBuilder;

trait JoinsTrait
{
    /** @var array joins */
    public $joins = [];


    // Help methods

    /**
     * Создание join.
     * @param string тип join
     * @param array [таблица] или [подзапрос, псевдоним]
     * @return JoinBuilder
     */
    protected function makeJoin(string $type, array $table): JoinBuilder
    {
        @[$query, $as] = $table;
        // return new JoinBuilder($this, $type, $query, $as);
        return new JoinBuilder($this->db, $type, $query, $as);
    }

    /**
     * Завершение сборки join.
     * @param JoinBuilder сборщик join
     * @param array условие
     * @param bool|null использовать ли WHERE вместо ON
     * @return self
     */
    protected function endJoin(JoinBuilder $join, array $condition, bool $where = false)
    {
        @[$first, $operator, $second] = $condition;
        if ($first instanceof \Closure) {
            $first($join);
        } else {
            if (count($condition) === 1 && is_string($first)) $join->using($first);
            else if ($where) $this->whereColumn(...$condition);
            else $join->on(...$condition);
        }
        return $this->addJoin($join);
    }

    /**
     * Добавление join.
     * @param JoinBuilder сборщик join
     * @return self
     */
    protected function addJoin(JoinBuilder $join)
    {
        $bindings = $join->getBindings();
        if (count($bindings)) $this->addBindings('join', $bindings);
        $this->joins[] = $join;
        return $this;
    }

    /**
     * Реальная установка всевозможных join.
     * @param string тип join
     * @param array [таблица] или [подзапрос, псевдоним]
     * @param array условие
     * @param bool|null использовать ли WHERE вместо ON
     * @return self
     */
    protected function realSetJoin(
        string $type, array $table, array $condition, bool $where = false
    ) {
        return $this->endJoin(
            $this->makeJoin($type, $table),
            $condition, $where
        );
    }

    /**
     * Установка JOIN ON.
     * @param string тип join
     * @param string таблица
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    protected function setJoin(
        string $type, string $table, $first, string $operator = null, string $second = null
    ) {
        $condition = func_get_args();
        return $this->realSetJoin(array_shift($condition), [array_shift($condition)], $condition);
    }

    /**
     * Установка JOIN ON с подзапросом таблицы.
     * @param string тип join
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    protected function setJoinSub(
        string $type, $query, string $as, $first, string $operator = null, string $second = null
    ) {
        $condition = func_get_args();
        return $this->realSetJoin(array_shift($condition), [
            array_shift($condition), array_shift($condition)
        ], $condition);
    }


    // ----------
    // INNER JOIN
    // ----------

    /**
     * Добавление INNER JOIN ON.
     * @param string таблица
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function join(string $table, $first, string $operator = null, string $second = null)
    {
        return $this->setJoin('INNER', ...func_get_args());
    }

    /**
     * Добавление INNER JOIN ON с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function joinSub(
        $query, string $as, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoinSub('INNER', ...func_get_args());
    }

    // ----------
    // LEFT JOIN
    // ----------

    /**
     * Добавление LEFT JOIN ON.
     * @param string таблица
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function leftJoin(
        string $table, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoin('LEFT', ...func_get_args());
    }

    /**
     * Добавление LEFT JOIN ON с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function leftJoinSub(
        $query, string $as, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoinSub('LEFT', ...func_get_args());
    }

    // ----------
    // LEFT OUTER JOIN
    // ----------

    /**
     * Добавление LEFT OUTER JOIN ON.
     * @param string таблица
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function leftOuterJoin(
        string $table, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoin('LEFT OUTER', ...func_get_args());
    }

    /**
     * Добавление LEFT OUTER JOIN ON с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function leftOuterJoinSub(
        $query, string $as, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoinSub('LEFT OUTER', ...func_get_args());
    }

    // ----------
    // RIGHT JOIN
    // ----------

    /**
     * Добавление RIGHT JOIN ON.
     * @param string таблица
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function rightJoin(
        string $table, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoin('RIGHT', ...func_get_args());
    }

    /**
     * Добавление RIGHT JOIN ON с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function rightJoinSub(
        $query, string $as, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoinSub('RIGHT', ...func_get_args());
    }


    // ----------
    // RIGHT OUTER JOIN
    // ----------

    /**
     * Добавление RIGHT OUTER JOIN ON.
     * @param string таблица
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function rightOuterJoin(
        string $table, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoin('RIGHT OUTER', ...func_get_args());
    }

    /**
     * Добавление RIGHT OUTER JOIN ON с подзапросом таблицы.
     * @param string|Queryable подзапрос таблицы
     * @param string псевдоним
     * @param string|\Closure первый столбец или колбек для JoinBuilder
     * @param string|null оператор, второй столбец или null
     * @param string|null второй столбец или null
     * @return self
     */
    public function rightOuterJoinSub(
        $query, string $as, $first, string $operator = null, string $second = null
    ) {
        return $this->setJoinSub('RIGHT OUTER', ...func_get_args());
    }
}
