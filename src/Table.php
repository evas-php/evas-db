<?php
/**
 * Класс таблицы базы данных с реализацией CRUD и поддержкой схемы.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Builders\QueryBuilder;
use Evas\Db\Schema\TableSchema;
use Evas\Db\Interfaces\QueryBuilderInterface;
use Evas\Db\Interfaces\QueryResultInterface;

class Table extends TableSchema
{
    /**
     * Начало сборки INSERT-запроса.
     * @param array|object|null значения записи/записей для сохранения с автосборкой
     * @return InsertBuilder|QueryResultInterface
     */
    public function insert($row = null)
    {
        return $this->db->insert($this->name, $row);
    }

    /**
     * Вставка нескольких записей.
     * @param array значения записей
     * @param array|null ключи записи
     * @return QueryResultInterface
     */
    public function batchInsert(array $rows, array $keys = null): QueryResultInterface
    {
        return $this->db->batchInsert($this->name, $rows, $keys);
    }

    // /**
    //  * Получение id последней вставленной записи.
    //  * @return int
    //  */
    // public function lastInsertId(): int
    // {
    //     return $this->db->lastInsertId($this->name);
    // }

    /**
     * Проброс методов QueryBuilder через магию php.
     * @param string имя метода
     * @param array|null аргументы
     * @return mixed
     */
    public function __call(string $name, array $args = null)
    {
        if (method_exists(QueryBuilder::class, $name)) {
            return (new QueryBuilder($this->db))->from($this->name)->$name(...$args);
        }
        throw new \BadMethodCallException(sprintf(
            'Call to undefined method %s()', __METHOD__
        ));
    }
}
