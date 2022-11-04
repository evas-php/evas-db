<?php
/**
 * Интерфейс таблицы базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

interface TableInterface
{
    /**
     * Получение первичного ключа таблицы.
     * @return string
     */
    public function primaryKey(): string;

    /**
     * Получение столбцов таблицы.
     * @return array
     */
    public function columns(): array;

    /**
     * Получение внешних ключей таблицы.
     * @return array
     */
    public function foreignKeys(): array;

    /**
     * Получение id последней вставленной записи.
     * @return int
     */
    public function lastInsertId(): int;

    /**
     * Проброс методов сборщика запросов через магию php.
     * @param string имя метода
     * @param array|null аргументы
     * @return mixed
     */
    public function __call(string $name, array $args = null);

    /**
     * Вставка записи или начало сборки sql-запроса на вставку.
     * @param array|object|null значения записи/записей для сохранения с автосборкой
     * @return InsertBuilderInterface|QueryResultInterface
     */
    public function insert($row = null): object;
}
