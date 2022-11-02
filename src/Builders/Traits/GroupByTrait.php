<?php
/**
 * Трейт сборки GROUP BY части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait GroupByTrait
{
    /** @var array поля группировки */
    public $groups = [];

    /**
     * Добавление группировки.
     * @param string ...группы
     * @return self
     */
    public function groupBy(string ...$groups)
    {
        $this->groups = array_merge($this->groups, array_map([$this, 'wrap'], $groups));
        return $this;
    }

    /**
     * Добавление группировки sql-строкой.
     * @param string sql
     * @param array|null экранируемые значения
     * @return self
     */
    public function groupByRaw(string $sql, array $bindings = [])
    {
        $this->groups[] = $sql;
        $this->addBindings('groups', $bindings);
        return $this;
    }
}
