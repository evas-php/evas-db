<?php
/**
 * Класс исключения маппинга идентичности сущностей IdentityMap.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Exceptions;

use Evas\Db\Exceptions\DbException;

class IdentityMapException extends DbException
{
    /**
     * Выбрасывание исключения IdentityMap вместе с информацией.
     * @param string сообщение ошибки
     * @param array информация о сущности
     * @param string|int|null значение первичного ключа сущности
     */
    public static function withInfo(string $error, array $entity, $primary = null)
    {
        if ($primary) $entity['primaryValue'] = $primary;
        $data = [
            'error' => $error,
            'entity' => $entity,
        ];
        return new static(json_encode($data));
    }
}
