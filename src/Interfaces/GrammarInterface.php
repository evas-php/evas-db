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
     * @param DatabaseInterface $db
     */
    public function __construct(DatabaseInterface &$db);

    /**
     * Убирает оборачивание ключа (таблица/столбец).
     * @param string $key обернутый ключ
     * @return string
     */
    public function unwrap(string $key): string;

    /**
     * Оборачивает ключ (таблица/столбец).
     * @param string $key ключ
     * @return string
     */
    public function wrap(string $key): string;

    /**
     * Оборачивает сегмент ключа (таблица/столбец).
     * @param string $key ключ
     * @return string
     */
    public function wrapOne(string $key): string;

    /**
     * Оборачивает столбцы в готовые для sql столбцы в виде строки.
     * @param array|string $columns столбцы
     * @return string
     */
    public function wrapColumns(array|string $columns): string;

    // Настройка соединения

    /**
     * Установливает кодировку.
     * @param string $charset кодировка
     */
    public function setCharset(string $charset);

    /**
     * Установливает таймзону.
     * @param string $timezone таймзона
     */
    public function setTimezone(string $timezone);

    /**
     * Переключает базу данных.
     * @param string $dbname имя базы данных
     */
    public function changeDbName(string $dbname);


    // Кэш схемы

    /**
     * Возвращает спискок таблиц.
     * @return array
     */
    public function getTablesList(): array;

    /**
     * Возвращает первичный ключ таблицы.
     * @param string $table имя таблицы
     * @return string
     */
    public function getTablePrimaryKey(string $table): string;

    /**
     * Возвращает столбцы таблицы в едином формате.
     * @param string $table имя таблицы
     * @return array<string, array{
     *     name: string, 
     *     type: string, 
     *     length: string, 
     *     dbType: string, 
     *     null: bool, 
     *     default: string,
     *     key: string, 
     *     indexes: array{schema: string, table: string, column: string}, 
     *     extra: array<string, bool>,
     *     dbExtra: string
     * }>
     */
    public function getTableColumns(string $table): array;

    /**
     * Возвращает список индексов таблицы в едином формате.
     * @param string $table имя таблицы
     * @return array<string, array{type: string, columns: string[]}>
     */
    public function getTableIndexes(string $table): array;

    /**
     * Возвращает индексы по колонкам и определяет их позиции в составных ключах.
     * @param string|array<string, array{type: string, columns: string[]}> $tableOrIndexes
     * @return array<string, array<string, array{type: string, columns: string[], position: int}>>
     */
    public function getIndexesByColumns(string|array $tableOrIndexes): array;

    /**
     * Возвращает внешние ключи таблицы.
     * @param string $table имя таблицы
     * @return array<string, array{schema: string, table: string, column: string}>
     */
    public function getForeignKeys(string $table): array;


    // Сборка запросов

    /**
     * Сборирает insert запрос.
     * @param InsertBuilderInterface $builder
     * @return string
     */
    public function buildInsert(InsertBuilderInterface &$builder): string;

    /**
     * Сборирает select/update/delete запрос.
     * @param QueryBuilderInterface $builder
     * @return string
     */
    public function buildQuery(QueryBuilderInterface &$builder): string;
}
