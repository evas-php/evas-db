<?php
/**
 * Интерфейс результата запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use \PDOStatement;
use Evas\Db\Interfaces\DatabaseInterface;

interface QueryResultInterface
{
    /**
     * Конструктор.
     * @param PDOStatement
     * @param DatabaseInterface
     */
    public function __construct(PDOStatement &$stmt, DatabaseInterface &$database);

    /**
     * Получение statement ответа базы.
     * @return PDOStatement
     */
    public function stmt(): PDOStatement;

    /**
     * Получение имени таблицы для select-запроса.
     * @return string|null
     */
    public function tableName(): ?string;

    /**
     * Получение количества возвращённых строк.
     * @return int
     */
    public function rowCount(): int;


    // Получение записи/записей в разном виде.

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
     * Получение записи в виде анонимного объекта.
     * @return stdClass|null
     */
    public function anonymObject(): ?object;

    /**
     * Получение всех записей в виде массива анонимных объектов.
     * @return array
     */
    public function anonymObjectAll(): array;

    /**
     * Получение записи в виде объекта класса.
     * @param string имя класса
     * @return object|null
     */
    public function classObject(string $className): ?object;

    /**
     * Получение всех записей в виде массива объектов класса.
     * @param string имя класса
     * @return array
     */
    public function classObjectAll(string $className): array;

    /**
     * Добавление параметров записи в объект.
     * @param object
     * @return object
     */
    public function intoObject(object &$object): object;


    // Хуки

    /**
     * Хук для постобработки полученного объекта записи.
     * @param object|null запись
     * @return object|null постобработанная запись
     */
    public function objectHook(object &$row = null): ?object;

    /**
     * Хук для постобработки полученных объектов записей.
     * @param array|null записи
     * @return array|null постобработанные записи
     */
    public function objectsHook(array &$rows = null): ?array;
}
