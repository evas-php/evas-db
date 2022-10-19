<?php
/**
 * Трейт сборки GROUP BY части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\GroupByOption;

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
        $this->groups = array_merge($this->groups, GroupByOption::columns(...$groups));
        return $this;
    }

    /**
     * Добавление группировки sql-строкой.
     * @param string sql
     * @param array|null экранируемые значения
     * @return self
     */
    public function groupByRaw(string $sql, array $values = [])
    {
        $this->groups[] = GroupByOption::raw($sql, $values);
        return $this;
    }
}
