<?php
/**
 * Класс-обертка над PDOStatement для получения ответа запроса в удобном виде.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Base;

use \PDO;
use \PDOStatement;
use Evas\Db\Exceptions\DbException;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Exceptions\IdentityMapException;
use Evas\Db\Interfaces\QueryResultInterface;

class QueryResult implements QueryResultInterface
{
    /** @var PDOStatement */
    protected $stmt;

    /** @var DatabaseInterface */
    protected $db;

    /**
     * Конструктор.
     * @param PDOStatement
     * @param DatabaseInterface
     */
    public function __construct(PDOStatement &$stmt, DatabaseInterface &$db)
    {
        $this->stmt = &$stmt;
        $this->db = &$db;
    }

    /**
     * Деструтор.
     */
    public function __destruct()
    {
        $this->stmt()->closeCursor();
    }

    /**
     * Получение statement ответа базы.
     * @return PDOStatement
     */
    public function stmt(): PDOStatement
    {
        return $this->stmt;
    }

    /**
     * Получение имени таблицы для select-запроса.
     * @return string|null
     */
    public function tableName(): ?string
    {
        if (0 > $this->stmt->columnCount()) return null;
        $columnMeta = $this->stmt->getColumnMeta(0);
        return $columnMeta['table'] ?? null;
    }

    /**
     * Получение количества возвращённых строк.
     * @return int
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Обертка метода fetch.
     * @return mixed|null данные записи
     * @throws DbException
     */
    protected function fetch(int $mode = null)
    {
        if ($this->rowCount() > 0) {
            $result = $this->stmt->fetch($mode);
            if (false === $result) {
                if (strpos($this->stmt->queryString, 'INSERT') !== false) 
                    throw new DbException("Insert query returns no rows");
                else 
                    throw new DbException('Failed to get data from previous sql query due to buffer overwriting');
            }
        }
        return $result ?? null;
    }

    /**
     * Обертка метода fetchAll.
     * @return array массив данных записей
     */
    protected function fetchAll(int $mode = null): array
    {
        if ($this->rowCount() > 0) {
            $result = $this->stmt->fetchAll($mode);
            if (empty($result)) {
                throw new DbException('Failed to get data from previous sql query due to buffer overwriting');
            }
        }
        return $result ?? [];
    }


    // Получение записи/записей в разном виде.

    /**
     * Получение записи в виде нумерованного массива.
     * @return numericArray|null
     */
    public function numericArray(): ?array
    {
        return $this->fetch(PDO::FETCH_NUM);
    }

    /**
     * Получение всех записей в виде массива нумерованных массивов.
     * @return array
     */
    public function numericArrayAll(): array
    {
        return $this->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Получение записи в виде ассоциативного массива.
     * @return assocArray|null
     */
    public function assocArray(): ?array
    {
        return $this->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Получение всех записей в виде массива ассоциативных массивов.
     * @return array
     */
    public function assocArrayAll(): array
    {
        return $this->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получение записи в виде анонимного объекта.
     * @return stdClass|null
     */
    public function anonymObject(): ?object
    {
        $row = $this->fetch(PDO::FETCH_OBJ);
        return $this->objectHook($row);
    }

    /**
     * Получение всех записей в виде массива анонимных объектов.
     * @return array
     */
    public function anonymObjectAll(): array
    {
        $rows = $this->fetchAll(PDO::FETCH_OBJ);
        return $this->objectsHook($rows);
    }

    /**
     * Получение записи в виде объекта класса.
     * @param string имя класса
     * @return object|null
     */
    public function classObject(string $className): ?object
    {
        if (0 === $this->rowCount()) return null;
        $this->stmt->setFetchMode(PDO::FETCH_CLASS, $className);
        $row = $this->fetch();
        return $this->objectHook($row);
    }

    /**
     * Получение всех записей в виде массива объектов класса.
     * @param string имя класса
     * @return array
     */
    public function classObjectAll(string $className): array
    {
        if (0 === $this->rowCount()) return [];
        $this->stmt->setFetchMode(PDO::FETCH_CLASS, $className);
        $rows = $this->fetchAll();
        return $this->objectsHook($rows);
    }

    /**
     * Добавление параметров записи в объект.
     * @param object
     * @return object
     */
    public function intoObject(object &$object): object
    {
        if (0 === $this->rowCount()) return $object;
        $this->stmt->setFetchMode(PDO::FETCH_INTO, $object);
        $this->fetch();
        return $this->objectHook($object);
    }


    // Хуки


    /**
     * Хук для постобработки полученного объекта записи.
     * @param object|null запись
     * @return object|null постобработанная запись
     */
    public function objectHook(object &$row = null): ?object
    {
        if (!empty($row) && is_object($row)) {
            $this->identityMapUpdate($row);
            $this->runEntityAfterFindMethod($row);
        }
        return $row;
    }

    /**
     * Хук для постобработки полученных объектов записей.
     * @param array|null записи
     * @return array|null постобработанные записи
     */
    public function objectsHook(array &$rows = null): ?array
    {
        if (!empty($rows) && ($this->canIdentityMapUpdate()
            || $this->canRunEntityAfterFindMethod($row[0])))
            foreach ($rows as &$row) {
                $row = $this->objectHook($row);
            }
        return $rows;
    }

    // Добавленные хуки

    /**
     * Проверка возможности запуска метода afterFind сущности.
     * @param object сущность
     * @return bool
     */
    public function canRunEntityAfterFindMethod(object &$row): bool
    {
        return method_exists($row, 'afterFind');
    }

    /**
     * Запуска метода afterFind сущности, если есть.
     * @param object сущность
     */
    public function runEntityAfterFindMethod(object &$row)
    {
        if ($this->canRunEntityAfterFindMethod($row)) $row->afterFind();
    }

    /**
     * Проверка возможности обновления IdentityMap.
     * @return bool
     */
    public function canIdentityMapUpdate(): bool
    {
        return method_exists($this->db, 'table') 
            && method_exists($this->db, 'identityMapUpdate');
    }

    /**
     * Хук для обновления сущности в IdentityMap.
     * @param object сущность
     */
    public function identityMapUpdate(object &$object)
    {
        if ($this->canIdentityMapUpdate()) {
            $primaryKey = $this->db->table($this->tableName())->primaryKey();
            try {
                $object = $this->db->identityMapUpdate($object, $primaryKey);
            } catch (IdentityMapException $e) {
                if ($this->db->isStrictPrimary()) throw $e;
                else return;
            }
        }
    }
}
