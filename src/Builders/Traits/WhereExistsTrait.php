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
     * @param \Closure|string|self подзапрос
     * @param bool использовать ли OR для склейки where
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function whereExists($query, bool $isOr = false, bool $isNot = false)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->pushWhere('Exists', compact('sql', 'bindings', 'isOr', 'isNot'));
    }

    /**
     * Добавление where OR exists.
     * @param \Closure|string|self подзапрос
     * @return self
     */
    public function orWhereExists($query)
    {
        return $this->whereExists($query, true);
    }

    /**
     * Добавление where AND NOT exists.
     * @param \Closure|string|self подзапрос
     * @return self
     */
    public function whereNotExists($query)
    {
        return $this->whereExists($query, false, true);
    }

    /**
     * Добавление where OR NOT exists.
     * @param \Closure|string|self подзапрос
     * @return self
     */
    public function orWhereNotExists($query)
    {
        return $this->whereExists($query, true, true);
    }
}
