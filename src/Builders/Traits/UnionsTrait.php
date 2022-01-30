<?php
/**
 * Трейт добавления unions.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Base\Help\PhpHelp;

trait UnionsTrait
{
    /**
     * Добавление union.
     * @param \Closure|self|string колбэк, сборщик запроса или sql-запрос
     * @param bool|null установить ли ALL
     * @return self
     */
    public function union($query, bool $all = false)
    {
        if (!$this->isQueryable($query)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be queryable, %s given',
                __METHOD__, PhpHelp::getType($query)
            ));
        }
        if ($query instanceof \Closure) {
            call_user_func($query, $query = $this->newQuery());
        }
        // [$sql, $bindings] = $this->createSub($query);
        $this->unions[] = compact('query', 'all');
        return $this->addBindings('union', $query->getBindings());
    }

    /**
     * Добавление union ALL.
     * @param \Closure|self|string колбэк, сборщик запроса или sql-запрос
     * @return self
     */
    public function unionAll($query)
    {
        return $this->union($query, true);
    }
}
