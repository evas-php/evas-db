<?php
/**
 * Базовыйй интерфейс соединения с базой данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\GrammarInterface;
use Evas\Db\Interfaces\QueryResultInterface;

interface BaseDatabaseInterface
{
    /**
     * Конструктор.
     * @param array|null параметры соединения
     */
    public function __construct(array $props = null);


    // Работа с соединением

    /**
     * Открытие соединения.
     * @return self
     * @throws DatabaseConnectionException
     */
    public function open(): DatabaseInterface;

    /**
     * Закрытие соединения.
     * @return self
     */
    public function close(): DatabaseInterface;

    /**
     * Проверка открытости соединения.
     * @return bool
     */
    public function isOpen(): bool;

    /**
     * Получение PDO.
     * @return \PDO
     * @throws DatabaseConnectionException
     */
    public function getPdo(): \PDO;

    /**
     * Установка кодировки.
     * @param string кодировка
     * @return self
     */
    public function setCharset(string $charset);

    /**
     * Установка таймзоны.
     * @param string кодировка
     * @return self
     */
    public function setTimezone(string $timezone);

    /**
     * Переключение на базу данных.
     * @param string имя базы данных
     * @return self
     */
    public function changeDbName(string $dbname);


    // Доолнительные сущности

    /**
     * Получение СУБД-грамматики соединения.
     * @return GrammarInterface
     */
    public function grammar(): GrammarInterface;


    // Работа с транзакциями

    /**
     * Проверка открытости транзакции.
     * @return bool
     */
    public function inTransaction(): bool;

    /**
     * Создание транзакции.
     * @return self
     */
    public function beginTransaction(): DatabaseInterface;

    /**
     * Отмена транзакции.
     * @return self
     */
    public function rollBack(): DatabaseInterface;

    /**
     * Коммит транзакции.
     * @return self
     */
    public function commit(): DatabaseInterface;

    /**
     * Выполнение функции в транзакции с коммитом в конце.
     * @param \Closure колбек-функция для выполнения внутри транзакции
     * @return self
     */
    public function transaction(\Closure $callback): DatabaseInterface;


    // Работа с запросами

    /**
     * Получение подготовленного запроса.
     * @param string sql-запрос
     * @return \PDOStatement подготовленный запрос
     * @throws DatabaseQueryException
     */
    public function prepare(string $sql): \PDOStatement;

    /**
     * Выполнение подготовленного запроса.
     * @param \PDOStatement подготовленный запрос
     * @param array|null экранируемые параметры запроса
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function execute(\PDOStatement $stmt, array $props = null): QueryResultInterface;

    /**
     * Выполнение запроса с возвращением количества затронутых строк.
     * @param string sql-запрос
     * @return int кол-во затронутых строк
     * @throws DatabaseQueryException
     */
    public function pdoExec(string $sql): int;

    /**
     * Выполнение запроса без подготовки.
     * @param string sql-запрос
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function pdoQuery(string $sql): QueryResultInterface;

    /**
     * Выполнение запроса с автоподготовкой.
     * @param string sql-запрос
     * @param array|null экранируемые параметры запроса
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function query(string $sql, array $props = null): QueryResultInterface;

    /**
     * Выполнение нескольких запросов.
     * @param array sql-запросы
     * @param array|null экранируемые параметры запроса
     * @return array of QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function batchQuery(array $sqls, array $props = null): array;


    // 

    /**
     * Получение id последней вставленной записи.
     * @param string|null имя таблицы
     * @return int
     */
    public function lastInsertId(string $table = null): int;


    // Работа с ошибками

    /**
     * Получить расширенную информацию об ошибке последнего запроса.
     * @return array|null
     */
    public function errorInfo(): ?array;

    /**
     * Дебаг запроса.
     * @param string sql
     * @param array|null параметры запроса
     */
    public function debugSql(string $sql, array $props = null);
}
