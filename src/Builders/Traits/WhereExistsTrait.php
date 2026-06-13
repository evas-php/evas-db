<?php
/**
 * Трейт добавления where exists.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereExistsTrait
{
    /**
     * Добавление where exists.
     * По умолчанию склейка через AND.
     * @param string|\Closure|self подзапрос
     * @param array|null экранируемые значения
     * @param bool использовать ли OR для склейки where
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function whereExists(
        $query, array $bindings = [], bool $isOr = false, bool $isNot = false
    ) {
        [$sql, $bindings] = $this->createSub($query, $bindings);
        return $this->pushWhere('Exists', compact('sql', 'bindings', 'isOr', 'isNot'));
    }

    /**
     * Добавление where OR exists.
     * @param string|\Closure|self подзапрос
     * @param array|null экранируемые значения
     * @return self
     */
    public function orWhereExists($query, array $bindings = [])
    {
        return $this->whereExists($query, $bindings, true);
    }

    /**
     * Добавление where AND NOT exists.
     * @param string|\Closure|self подзапрос
     * @param array|null экранируемые значения
     * @return self
     */
    public function whereNotExists($query, array $bindings = [])
    {
        return $this->whereExists($query, $bindings, false, true);
    }

    /**
     * Добавление where OR NOT exists.
     * @param string|\Closure|self подзапрос
     * @param array|null экранируемые значения
     * @return self
     */
    public function orWhereNotExists($query, array $bindings = [])
    {
        return $this->whereExists($query, $bindings, true, true);
    }
}
