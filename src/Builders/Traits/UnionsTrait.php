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
    public $unions = [];
    
    /**
     * Добавление union.
     * @param string|\Closure|self подзапрос
     * @param array|null экранируемые значения для string-подзароса
     * @param bool|null установить ли ALL
     * @return self
     */
    public function union($query, array $bindings = [], bool $all = false)
    {
        if (!$this->isQueryable($query) && !is_string($query)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be a string or a queryable, %s given',
                __METHOD__, PhpHelp::getType($query)
            ));
        }
        [$sql, $bindings] = $this->createSub($query, $bindings);
        $this->unions[] = compact('sql', 'all');
        return $this->addBindings('unions', $bindings);
    }

    /**
     * Добавление union ALL.
     * @param string|\Closure|self подзапрос
     * @return self
     */
    public function unionAll($query, array $bindings = [])
    {
        return $this->union($query, $bindings, true);
    }
}
