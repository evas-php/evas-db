<?php
/**
 * Трейт расширения базы данных подддержкой сборщиков запросов для CRUD.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Builders\InsertBuilder;
use Evas\Db\Builders\QueryBuilder;
use Evas\Db\Interfaces\QueryBuilderInterface;
use Evas\Db\Interfaces\QueryResultInterface;

trait DatabaseBuildersTrait
{
    /**
     * Вызов сборщика запросов QueryBuilder для SELECT/UPDATE/DELETE-запросов.
     * @return QueryBulder
     */
    public function buildQuery(): QueryBuilderInterface
    {
        return new QueryBuilder($this);
    }

    /**
     * Начало сборки INSERT-запроса.
     * @param string имя таблицы
     * @param array|object|null значения записи для сохранения с автосборкой
     * @return InsertBuilder|QueryResultInterface
     */
    public function insert(string $tbl, $row = null): object
    {
        $ib = new InsertBuilder($this, $tbl);
        return empty($row) ? $ib : $ib->row($row)->query();
    }

    /**
     * Вставка нескольких записей.
     * @param string имя таблицы
     * @param array значения записей
     * @param array|null столбцы записи
     * @return QueryResultInterface
     */
    public function batchInsert(string $tbl, array $rows, array $columns): QueryResultInterface
    {
        $ib = $this->insert($tbl);
        if (!empty($columns)) $ib->keys($columns);
        return $ib->rows($rows)->query();
    }

    /**
     * Начало сборки SELECT-запроса.
     * @param string имя таблицы
     * @param string|null столбцы
     * @return QueryBuilder
     */
    public function select(string $tbl, string $columns = null): QueryBuilderInterface
    {
        return $this->buildQuery()->select($tbl, $columns);
    }

    /**
     * Начало сборки UPDATE-запроса.
     * @param string имя таблицы
     * @param array|object значения записи
     * @return QueryBuilder
     */
    public function update(string $tbl, $row): QueryBuilderInterface
    {
        return $this->buildQuery()->update($tbl, $row);
    }

    /**
     * Начало сборки DELETE-запроса.
     * @param string имя таблицы
     * @return QueryBuilder
     */
    public function delete(string $tbl): QueryBuilderInterface
    {
        return $this->buildQuery()->delete($tbl);
    }
}
