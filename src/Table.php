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
    {
        return $this->db->lastInsertId($this->name);
    }

    /**
     * Проброс методов сборщика запросов через магию php.
     * @param string имя метода
     * @param array|null аргументы
     * @return mixed
     */
    public function __call(string $name, array $args = null)
    {}

    /**
     * Начало сборки INSERT-запроса.
     * @param array|object|null значения записи/записей для сохранения с автосборкой
     * @return InsertBuilderInterface|QueryResultInterface
     */
    public function insert($row = null): object
    {
        return $this->db->insert($this->name, $row);
    }

    /**
     * Вставка нескольких записей.
     * @param array значения записей
     * @param array|null столбцы записей
     * @return QueryResultInterface
     */
    public function batchInsert(array $rows, array $columns = null): QueryResultInterface
    {
        return $this->db->batchInsert($this->name, $rows, $columns);
    }
}
