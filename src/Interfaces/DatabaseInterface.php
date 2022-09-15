<?php
/**
 * Расширенный интерфейс соединения с базой данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\BaseDatabaseInterface;
use Evas\Db\Interfaces\InsertBuilderInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;
use Evas\Db\Interfaces\QueryResultInterface;

interface DatabaseInterface extends BaseDatabaseInterface
{
    /**
     * Начало сборки sql-запроса на вставку.
     * @param string имя таблицы
     * @return InsertBuilderInterface
     */
    public function buildInsert(string $table): InsertBuilderInterface;

    /**
     * Вставка записи или начало сборки sql-запроса на вставку.
     * @param string имя таблицы
     * @param array|object|null запись, если нужно вставить одну строку
     * @return InsertBuilderInterface|QueryResultInterface
     */
    public function insert(string $table, $row = null): object;

    /**
     * Вставка нескольких записей.
     * @param string имя таблицы
     * @param array записи
     * @param array|null столбцы записей
     * @return QueryResultInterface
     */
    public function batchInsert(string $table, array $rows, array $columns = null)
    : QueryResultInterface;

    /**
     * Начало сборки sql-зароса select/update/delete через сборщик запроса.
     * @param string имя таблицы
     * @return QueryBuilderInterface
     */
    public function buildQuery(string $table): QueryBuilderInterface;

    /**
     * Начало сборки sql-запроса select через сборщик запроса.
     * @param string имя таблицы
     * @param array|string|null столбцы
     * @return QueryBuilderInterface
     */
    public function select(string $table, $columns = null): QueryBuilderInterface;
}
