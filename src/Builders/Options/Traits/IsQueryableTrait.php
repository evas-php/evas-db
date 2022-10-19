<?php
namespace Evas\Db\Builders\Options\Traits;

trait IsQueryableTrait
{
    /**
     * Проверка на доступность подзапроса.
     * @param mixed проверяемая переменная
     * @return bool
     */
    protected static function isQueryable($query): bool
    {
        return $query instanceof \Closure || $query instanceof BaseQueryBuilder;
    }
}
