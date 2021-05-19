<?php
/**
 * Интерфейс сборщика запроса SELECT/UPDATE/DELETE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\JoinBuilderInterface;
use Evas\Db\Interfaces\QueryResultInterface;

interface QueryBuilderInterface
{
    /**
     * Конструктор.
     * @param DatabaseInterface
     */
    public function __construct(DatabaseInterface &$db);

    /**
     * Начало SELECT запроса.
     * @param string имя таблицы
     * @param string поля
     * @return self
     */
    public function select(string $tbl, string $columns = null): QueryBuilderInterface;

    /**
     * Начало DELETE запроса.
     * @param string имя таблицы
     * @return self
     */
    public function delete(string $tbl): QueryBuilderInterface;

    /**
     * Начало UPDATE запроса.
     * @param string имя таблицы
     * @param string|array|object значения записи или sql-запрос
     * @param array|null значения для экранирования используемые в sql-запросе
     * @return self
     */
    public function update(string $tbl, $row, array $vals = []): QueryBuilderInterface;


    /**
     * Установка части FROM.
     * @param string часть from
     * @param array|null параметры для экранирования
     * @return self
     */
    public function from(string $from, array $values = []): QueryBuilderInterface;

    /**
     * Запуск сборщика INNER JOIN.
     * @param string|null имя таблицы
     * @return JoinBuilderInterface
     */
    public function innerJoin(string $tbl = null): JoinBuilderInterface;

    /**
     * Запуск сборщика LEFT JOIN.
     * @param string|null имя таблицы
     * @return JoinBuilderInterface
     */
    public function leftJoin(string $tbl = null): JoinBuilderInterface;

    /**
     * Запуск сборщика RIGHT JOIN.
     * @param string|null имя таблицы
     * @return JoinBuilderInterface
     */
    public function rightJoin(string $tbl = null): JoinBuilderInterface;

    /**
     * Запуск сборщика OUTER JOIN.
     * @param string|null имя таблицы
     * @return JoinBuilderInterface
     */
    public function outerJoin(string $tbl = null): JoinBuilderInterface;

    /**
     * Запуск сборщика INNER JOIN (алиас для innerJoin).
     * @param string|null имя таблицы
     * @return JoinBuilderInterface
     */
    public function join(string $tbl = null): JoinBuilderInterface;

    /**
     * Добавление JOIN с помощью sql.
     * @param string join sql
     * @param array параметры для экранирования
     * @return self
     */
    public function setJoin(string $join, array $values = []): QueryBuilderInterface;


    /**
     * Установка WHERE.
     * @param string where часть
     * @param array параметры для экранирования
     * @return self
     */
    public function where(string $where, array $values = []): QueryBuilderInterface;

    /**
     * Установка WHERE IN.
     * @param string имя поля
     * @param array массив значений сопоставления
     * @return self
     */
    public function whereIn(string $key, array $values): QueryBuilderInterface;


    /**
     * Установка GROUP BY.
     * @param string столбцы группировки
     * @param string|null having условие
     * @param array|null параметра having для экранирования
     * @return self
     */
    public function groupBy(string $columns, string $having = null, array $havingValues = []): QueryBuilderInterface;

    /**
     * Установка HAVING.
     * @param string having условие
     * @param array параметры для экранирования
     * @return self
     */
    public function having(string $having, array $values = []): QueryBuilderInterface;

    /**
     * Установка ORDER BY.
     * @param string столбцы сортировки
     * @param bool|null сортировать по убыванию
     * @return self
     */
    public function orderBy(string $columns, bool $desc = false): QueryBuilderInterface;

    /**
     * Установка OFFSET.
     * @param int сдвиг
     * @return self
     */
    public function offset(int $offset): QueryBuilderInterface;

    /**
     * Установка LIMIT.
     * @param int лимит
     * @param int|null сдвиг
     * @return self
     */
    public function limit(int $limit, int $offset = null): QueryBuilderInterface;


    /**
     * Получение sql.
     * @return string
     */
    public function getSql(): string;

    /**
     * Получение одной записи.
     * @return QueryResultInterface
     */
    public function one(): object;

    /**
     * Получение записей.
     * @param int|null limit
     * @param int|null offset
     * @return QueryResultInterface
     */
    public function query(int $limit = null, int $offset = null): object;
}
