<?php
/**
 * Трейт добавления where exists.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\BaseQueryBuilder;

trait WhereExistsTrait
{
    /**
     * Добавление where exists подзапроса.
     * @param BaseQueryBuilder сборщик запроса
     * @param bool использовать ли OR для склейки where
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function addWhereExistsQuery(BaseQueryBuilder $query, bool $isOr = false, bool $isNot = false)
    {
        [$sql, $values] = $query->getSqlAndBindings();
        return $this->pushWhere('Exists', compact('sql', 'values', 'isOr', 'isNot'));
    }

    /**
     * Добавление where exists.
     * По умолчанию склейка через AND.
     * @param \Closure колбек подзапроса
     * @param bool использовать ли OR для склейки where
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function whereExists(\Closure $callback, bool $isOr = false, bool $isNot = false)
    {
        call_user_func($callback, $query = $this->forSubQuery());
        return $this->addWhereExistsQuery($query, $isOr, $isNot);
    }

    /**
     * Добавление where OR exists.
     * @param \Closure колбек подзапроса
     * @return self
     */
    public function orWhereExists(\Closure $callback)
    {
        return $this->whereExists($callback, true);
    }

    /**
     * Добавление where AND NOT exists.
     * @param \Closure колбек подзапроса
     * @return self
     */
    public function whereNotExists(\Closure $callback)
    {
        return $this->whereExists($callback, false, true);
    }

    /**
     * Добавление where OR NOT exists.
     * @param \Closure колбек подзапроса
     * @return self
     */
    public function orWhereNotExists(\Closure $callback)
    {
        return $this->whereExists($callback, true, true);
    }
}
