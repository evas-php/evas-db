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
     * Раскрытие оборачивания ключа (таблица/столбец).
     * @param string обернутый ключ
     * @return string ключ
     */
    public function unwrap(string $key): string;

    /**
     * Оборачивание ключа (таблица/столбец).
     * @param string ключ
     * @return string обернутый ключ
     */
    public function wrap(string $key): string;

    /**
     * Оборачивание сегмента ключа (таблица/столбец).
     * @param string ключ
     * @return string обернутый ключ
     */
    public function wrapOne(string $key): string;

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

    // Настройка соединения

    /**
     * Установка кодировки.
     * @param string кодировка
     */
    public function setCharset(string $charset);

    /**
     * Установка таймзоны.
     * @param string кодировка
     */
    public function setTimezone(string $timezone);

    /**
     * Переключение базы данных.
     * @param string имя базы данных
     */
    public function changeDbName(string $dbname);


    // Кэш схемы

    /**
     * Получение списка таблиц.
     * @return array таблицы
     */
    public function getTablesList(): array;

    /**
     * Получение первичного ключа таблицы.
     * @param string имя таблицы
     * @return string первичный ключ
     */
    public function getTablePrimaryKey(string $table): string;

    /**
     * Получение столбцов таблицы.
     * @param string имя таблицы
     * @return array столбцы
     */
    public function getTableColumns(string $table): array;

    /**
     * Получение внешних ключей таблицы.
     * @param string имя таблицы
     * @return array внешние ключи
     */
    public function getForeignKeys(string $table): array;


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
}
