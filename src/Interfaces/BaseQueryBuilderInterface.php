<?php
/**
 * Базовый интерфейс сборщика заросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryResultInterface;

interface BaseQueryBuilderInterface
{
    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     * @param string|null имя таблицы
     */
    public function __construct(DatabaseInterface &$db, string $table = null);


    /**
     * Установка FROM.
     * @param mixed таблицы или подзапрос
     * @param string|null алиас
     * @return BaseQueryBuilderInterface
     */
    public function from($table, string $as = null);


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
     * @return BaseQueryBuilderInterface|object|array of objects
     */
    public function query(); //: BaseQueryBuilderInterface;

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
     * @return BaseQueryBuilderInterface
     */
    public function delete($id = null); //: BaseQueryBuilderInterface;

    /**
     * Выолнение update-запроса обновления записи/записей sql-строкой.
     * @param string sql-запрос
     * @param array обновлённые данные
     * @return BaseQueryBuilderInterface
     */
    public function updateRaw(string $sql, array $vals); //: BaseQueryBuilderInterface;

    /**
     * Выолнение update-запроса обновления записи/записей.
     * @param array обновлённые данные
     * @return BaseQueryBuilderInterface
     */
    public function update(array $data); //: BaseQueryBuilderInterface;
}
