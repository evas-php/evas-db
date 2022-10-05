<?php
/**
 * Интерфейс сборщика заросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryResultInterface;

interface QueryBuilderInterface
{
    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     * @param string|null имя таблицы
     */
    public function __construct(DatabaseInterface &$db, string $table = null);


    // Получение данных для выполнения запроса

    /**
     * Получение собранного sql-запроса.
     * @return string
     */
    public function getSql(): string;

    /**
     * Получение экранируемых значений собранного запроса.
     * @param string|null для получения части экранируемых значений
     * @return array
     */
    public function getBindings(string $part = null): array;

    /**
     * Получение sql-запроса и экранируемых значений.
     * @return array [sql, values]
     */
    public function getSqlAndBindings(): array;


    // Выполнение запроса

    /**
     * Выполнение sql-запроса.
     * @return QueryResultInterface|object|array of objects
     */
    public function query(); //: QueryResultInterface;

    /**
     * Выполнение select-запроса с получением нескольких записей.
     * @param array|null столбцы для получения
     * @return array найденные записи
     */
    public function get($columns = null): array;

     /**
     * Выполнение select-запроса с получением одной записи.
     * @param array|null столбцы для получения
     * @return array|null найденная запись
     */
    public function one();

    /**
     * Поиск записи/записей по первичному ключу.
     * @param array|numeric|string значение первичного ключа
     * @param array|null запрашиваемые поля
     * @return array найденная или найденные записи
     * @throws \InvalidArgumentException
     */
    public function find($id, array $columns = ['*']);

    /**
     * Выолнение delete-запроса удаления записи/записей.
     * @param mixed|null id записи/записей, если нужно удалить конкретные
     * @return QueryResultInterface
     */
    public function delete($id = null); //: QueryResultInterface;

    /**
     * Выолнение update-запроса обновления записи/записей sql-строкой.
     * @param string sql-запрос
     * @param array обновлённые данные
     * @return QueryResultInterface
     */
    public function updateRaw(string $sql, array $vals); //: QueryResultInterface;

    /**
     * Выолнение update-запроса обновления записи/записей.
     * @param array обновлённые данные
     * @return QueryResultInterface
     */
    public function update(array $data); //: QueryResultInterface;
}
