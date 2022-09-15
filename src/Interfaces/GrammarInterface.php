<?php
/**
 * Интерфейс грамматики сборки запросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\InsertBuilderInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;

interface GrammarInterface
{
    /**
     * Конструктор.
     * @param DatabaseInterface
     */
    public function __construct(DatabaseInterface &$db);

    /**
     * Оборачивание значения.
     * @param string значение
     * @return string обернутое значение
     */
    public function wrap(string $value): string;

    /**
     * Раскрытие оборачивания значения.
     * @param string обернутое значение
     * @return string значение
     */
    public function unwrap(string $value): string;

    /**
     * Оборачивание столбцов в готовые sql-столбцы.
     * @param array столбцы
     * @return string готовые sql-столбцами
     */
    public function wrapColumns(array $columns): string;

    /**
     * Оборачивание столбцов переданных строкой в готовые sql-столбцы.
     * @param array столбцы в виде строки
     * @return string готовые sql-столбцами
     */
    public function wrapStringColumns(string $value): string;


    // Кэш схемы

    /**
     * Получение списка таблиц.
     */
    public function getTablesList(): array;

    /**
     * Получение первичного ключа таблицы.
     * @param string имя таблицы
     */
    public function getTablePrimaryKey(string $table): ?string;


    /**
     * Получение столбцов таблицы.
     * @param string имя таблицы
     */
    public function getTableColumns(string $table);

    /**
     * Получение внешних ключей таблицы.
     * @param string имя таблицы
     */
    public function getForeignKeys(string $table);


    // 

    /**
     * Переключение базы данных.
     * @param string имя базы данных
     */
    public function changeDbName(string $dbname);

    /**
     * Установка кодировки.
     * @param string кодировка
     */
    public function setCharset(string $charset);


    // Сборка запросов

    /**
     * Сборка insert запроса.
     * @param InsertBuilderInterface
     * @return string готовый insert запрос
     */
    public function buildInsert(InsertBuilderInterface &$builder): string;

    /**
     * Сборка select/update/delete запроса.
     * @param QueryBuilderInterface
     * @return string готовый select запрос
     */
    public function buildQuery(QueryBuilderInterface &$builder): string;


    // Частичная сборка select-запроса

    /**
     * Сборка Where или Having части sql.
     * @param array части where или having
     * @return string готовый sql-where или sql-having
     */
    public function buildWheres(array &$wheres): string;

    /**
     * Сборка join.
     * @param 
     * @return string готовый sql-join
     */
    public function buildJoin($join): string;

    /**
     * Сборка нескольких join.
     * @param 
     * @return string готовые sql-join
     */
    public function buildJoins(array $joins): string;

    /**
     * Сборка группировки.
     * @param array группировки
     * @return string готовый sql-group
     */
    public function buildGroups(array $groups): string;

    /**
     * Сборка объединения.
     * @param array union
     * @return string готовый sql-union
     */
    public function buildUnion(array $union): string;

    /**
     * Сборка нескольких объединений.
     * @param QueryBuilderInterface 
     * @return string готовые sql-union
     */
    public function buildUnions(QueryBuilderInterface &$builder): string;

    /**
     * Сборка сортировок.
     * @param array настроки сортировок
     * @return string готовый sql-order
     */
    public function buildOrders(array $orders): string;

    /**
     * Сборки лимита.
     * @param int|null лимит
     * @return string готовый sql-limit
     */
    public function buildLimit(int $limit = null): string;

    /**
     * Сборки сдвига.
     * @param int|null сдвиг
     * @return string готовый sql-offset
     */
    public function buildOffset(int $offset = null): string;

}
