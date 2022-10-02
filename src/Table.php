<?php
/**
 * Класс таблицы базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Interfaces\TableInterface;
use Evas\Db\SchemaCache\TableSchemaCache;

class Table extends TableSchemaCache implements TableInterface
{
    /**
     * Получение id последней вставленной записи.
     * @return int
     */
    public function lastInsertId(): int
    {}

    /**
     * Проброс методов сборщика запросов через магию php.
     * @param string имя метода
     * @param array|null аргументы
     * @return mixed
     */
    public function __call(string $name, array $args = null)
    {}
}
