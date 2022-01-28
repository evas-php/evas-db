<?php
/**
 * Исключение sql-запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Exceptions;

use Evas\Db\Exceptions\DbException;

class DatabaseQueryException extends DbException
{
    /**
     * Выбрасывание исключения sql-запроса с информацией.
     * @param array информация о падении запроса
     * @param string sql-запрос
     * @param array|null параметры sql-запроса для экранирования
     */
    public static function fromErrorInfo(array $errInfo, string $sql, array $props = null)
    {
        list($sqlState, $code, $message) = $errInfo;
        $data = [
            'error' => compact('code', 'message', 'sqlState'),
            'query' => $sql,
            'props' => $props ?? [],
        ];
        return new static(json_encode($data), $code);
    }
}
