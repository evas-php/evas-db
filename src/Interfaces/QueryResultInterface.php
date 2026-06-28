<?php
/**
 * Интерфейс результата запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\DatabaseInterface;

interface QueryResultInterface
{
    /**
     * Конструктор.
     * @param \PDOStatement
     * @param DatabaseInterface
     */
    public function __construct(\PDOStatement &$stmt, DatabaseInterface &$db);

    
    // Мета-информация

    /**
     * Получение statement ответа базы.
     * @return \PDOStatement
     */
    public function stmt(): \PDOStatement;

    /**
     * Получение имени таблицы select-запроса.
     * @return string|null
     */
    public function tableName(): ?string;

    /**
     * Получение количества возвращённых строк.
     * @return int
     */
    public function rowCount(): int;


    // Получение записи/записей в разном виде

    /**
     * Получение записи в виде нумерованного массива.
     * @return numericArray|null
     */
    public function numericArray(): ?array;

    /**
     * Получение всех записей в виде массива нумерованных массивов.
     * @return array
     */
    public function numericArrayAll(): array;

    /**
     * Получение записи в виде ассоциативного массива.
     * @return assocArray|null
     */
    public function assocArray(): ?array;

    /**
     * Получение всех записей в виде массива ассоциативных массивов.
     * @return array
     */
    public function assocArrayAll(): array;

    /**
     * Получение записи в виде объекта.
     * @param string|null имя класса, если он должен отличаться от stdClass
     * @return stdClass|null
     */
    public function object(string $className = null): ?object;

    /**
     * Получение всех записей в виде массива объектов.
     * @param string|null имя класса, если он должен отличаться от stdClass
     * @return array
     */
    public function objectAll(string $className = null): array;

    /**
     * Добавление параметров записи в объект.
     * @param object
     * @return object
     */
    public function intoObject(object &$object): object;

    
    // Хуки

    // 
}
