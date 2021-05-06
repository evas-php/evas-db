<?php
/**
 * Интерфейс сборщиков запросов базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\QueryBuilderInterface;
use Evas\Db\Interfaces\QueryResultInterface;

interface DatabaseQueryBuildersInterface
{
    /**
     * Начало сборки INSERT-запроса.
     * @param string имя таблицы
     * @param array|object|null значения записи для сохранения с автосборкой
     * @return InsertBuilder|QueryResultInterface
     */
    public function insert(string $tbl, $row = null): object;

    /**
     * Вставка нескольких записей.
     * @param string имя таблицы
     * @param array значения записей
     * @param array|null столбцы записи
     * @return QueryResultInterface
     */
    public function batchInsert(string $tbl, array $rows, array $columns): QueryResultInterface;

    /**
     * Начало сборки SELECT-запроса.
     * @param string имя таблицы
     * @param string|null столбцы
     * @return QueryBuilderInterface
     */
    public function select(string $tbl, string $columns = null): QueryBuilderInterface;

    /**
     * Начало сборки UPDATE-запроса.
     * @param string имя таблицы
     * @param array|object значения записи
     * @return QueryBuilderInterface
     */
    public function update(string $tbl, $row): QueryBuilderInterface;

    /**
     * Начало сборки DELETE-запроса.
     * @param string имя таблицы
     * @return QueryBuilderInterface
     */
    public function delete(string $tbl): QueryBuilderInterface;
}