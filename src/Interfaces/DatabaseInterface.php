<?php
/**
 * Интерфейс соединения с базой данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use \PDO;
use \PDOStatement;
use Evas\Db\Interfaces\QueryResultInterface;

interface DatabaseInterface
{
    /**
     * Открытие соединения.
     * @throws DatabaseConnectionException
     */
    public function open();

    /**
     * Закрытие соединения.
     */
    public function close();

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
    public function getPdo(): PDO;

    /**
     * Проверка открытости транзакции.
     * @return bool
     */
    public function inTransaction(): bool;

    /**
     * Создание транзакции.
     */
    public function beginTransaction();

    /**
     * Отмена транзакции.
     */
    public function rollBack();

    /**
     * Коммит транзакции.
     */
    public function commit();

    /**
     * Получение подготовленного запроса.
     * @param string sql-запрос
     * @return PDOStatement подготовленный запрос
     * @throws DatabasePrepareQueryException
     */
    public function prepare(string $sql): PDOStatement;

    /**
     * Выполнение подготовленного запроса.
     * @param PDOStatement подготовленный запрос
     * @param array|null экранируемые параметры запроса
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function execute(PDOStatement &$stmt, array $props = null): QueryResultInterface;

    /**
     * Выполнение запроса с автоподготовкой.
     * @param string sql-запрос
     * @param array|null экранируемые параметры запроса
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function query(string $sql, array $props = null): QueryResultInterface;

    /**
     * Получение id последней вставленной записи.
     * @param string|null имя таблицы
     * @return int|null
     */
    public function lastInsertId(string $tbl = null): ?int;
}
