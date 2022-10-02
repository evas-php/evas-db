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
use Evas\Db\Interfaces\SchemaCacheInterface;
use Evas\Db\Interfaces\TableInterface;

interface DatabaseInterface extends BaseDatabaseInterface
{
    // Работа с кэшем схемы БД

    /**
     * Получение кэша схемы БД.
     * @return SchemaCacheInterface
     */
    public function schemaCache(): SchemaCacheInterface;


    // Работа с таблицами

    /**
     * Получение списка таблиц базы данных.
     * @param bool перезапросить список таблиц
     * @return array|null
     */
    public function tablesList(bool $reload = false): ?array;

    /**
     * Получение объекта таблицы.
     * @param string имя таблицы
     * @return TableInterface
     */
    public function table(string $table): TableInterface;

    // Запросы через сборщики заросов.

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
    public function buildQuery(string $table = null): QueryBuilderInterface;

    /**
     * Начало сборки sql-запроса select через сборщик запроса.
     * @param string имя таблицы
     * @param array|string|null столбцы
     * @return QueryBuilderInterface
     */
    public function select(string $table, $columns = null): QueryBuilderInterface;
}
