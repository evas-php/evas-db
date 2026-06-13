<?php
/**
 * Абстрактный сборщик запросов SELECT/UPDATE/DELETE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Db\Builders\Traits\BindingsTrait;
use Evas\Db\Builders\Traits\FromTrait;
use Evas\Db\Builders\Traits\PrepareTrait;
use Evas\Db\Builders\Traits\SubQueryTrait;
use Evas\Db\Builders\Traits\WrapsTrait;

abstract class AbstractQueryBuilder
{
    use BindingsTrait;
    use FromTrait;
    use PrepareTrait;
    use SubQueryTrait;
    use WrapsTrait;

    /**
     * Проверка на доступность подзапроса.
     * @param mixed проверяемая переменная
     * @return bool
     */
    protected static function isQueryable($query): bool
    {
        return $query instanceof \Closure || $query instanceof static;
    }


    // Получение данных для выполнения запроса

    /**
     * Получение собранного sql-запроса.
     * @return string
     */
    abstract public function getSql(): string;

    /**
     * Получение sql-запроса и экранируемых значений.
     * @return array [sql, values]
     */
    public function getSqlAndBindings(): array
    {
        return [$this->getSql(), $this->getBindings()];
    }

    /**
     * Приведение сборщика запросов к строке.
     * @return string
     */
    public function __toString(): string
    {
        return $this->getSql();
    }
}
