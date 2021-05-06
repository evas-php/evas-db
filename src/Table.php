<?php
/**
 * Класс таблицы базы данных с реализацией CRUD и поддержкой схемы.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Schema\TableSchema;
use Evas\Db\Interfaces\QueryBuilderInterface;
use Evas\Db\Interfaces\QueryResultInterface;

class Table extends TableSchema
{
    /** @var int|null id последней вставленной записи */
    protected $lastInsertId;

    /**
     * Начало сборки INSERT-запроса.
     * @param array|object|null значения записи для сохранения с автосборкой
     * @return InsertBuilder|QueryResultInterface
     */
    public function insert($row = null): object
    {
        $this->db->insert($this->name, $row);
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

    /**
     * Начало сборки SELECT-запроса.
     * @param string|null столбцы
     * @return QueryBuilderInterface
     */
    public function select(string $columns = null): QueryBuilderInterface
    {
        return $this->db->select($this->name, $columns);
    }

    /**
     * Начало сборки UPDATE-запроса.
     * @param array|object значения записи
     * @return QueryBuilderInterface
     */
    public function update($row): QueryBuilderInterface
    {
        return $this->db->update($this->name, $row);
    }

    /**
     * Начало сборки DELETE-запроса.
     * @return QueryBuilderInterface
     */
    public function delete(): QueryBuilderInterface
    {
        return $this->db->delete($this->name);
    }

    /**
     * Получение id последней вставленной записи.
     * @return int|null
     */
    public function lastInsertId(): ?int
    {
        $lastInsertId = $this->db->lastInsertId($this->name);
        if (intval($lastInsertId) > intval($this->lastInsertId)) {
            $this->lastInsertId = $lastInsertId;
        }
        return $this->lastInsertId;
    }
}
