<?php
/**
 * Интерфейс сборщика insert-заросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryResultInterface;

interface InsertBuilderInterface
{
    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     * @param string имя таблицы
     */
    public function __construct(DatabaseInterface &$db, string $table);

    
    // Получение данных для выполнения запроса

    /**
     * Получение собранного sql-запроса.
     * @return string
     * @throws InsertBuilderException
     */
    public function getSql(): string;

    /**
     * Получение экранируемых значений собранного запроса.
     * @return array
     */
    public function getBindings(): array;


    // Выполнение запроса

    /**
     * Выполнение запроса.
     * @return QueryResultInterface
     */
    public function query(): QueryResultInterface;
}
