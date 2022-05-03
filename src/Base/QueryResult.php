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
     * @param int|null модификатор
     * @param mixed|null уточнение модификатора
     * @return mixed|null данные записи
     * @throws DbException
     */
    protected function fetch(int $mode = null, $modeMore = null)
    {
        if ($this->rowCount() > 0) {
            if (!empty($modeMore) && !empty($mode)) {
                $this->stmt->setFetchMode($mode, $modeMore);
                $mode = null;
            }
            $result = $this->stmt->fetch($mode);
            if (false === $result) {
                throw new DbException(
                    strpos($this->stmt->queryString, 'INSERT') !== false
                    ? 'Insert query returns no rows'
                    : 'Failed to get data from previous sql query due to buffer overwriting'
                );
            }
        }
        return $result ?? null;
    }

    /**
     * Обертка метода fetchAll.
     * @param int|null модификатор
     * @param mixed|null уточнение модификатора
     * @return array массив данных записей
     * @throws DbException
     */
    protected function fetchAll(int $mode = null, $modeMore = null): array
    {
        if ($this->rowCount() > 0) {
            if (!empty($modeMore) && !empty($mode)) {
                $this->stmt->setFetchMode($mode, $modeMore);
                $mode = null;
            }
            $result = $this->stmt->fetchAll($mode);
            if (empty($result)) {
                throw new DbException(
                    strpos($this->stmt->queryString, 'INSERT') !== false
                    ? 'Insert query returns no rows'
                    : 'Failed to get data from previous sql query due to buffer overwriting'
                );
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
     * Получение записи в виде объекта.
     * @param string|null имя класса
     * @return stdClass|null
     */
    public function object(string $className = null): ?object
    {
        if (0 === $this->rowCount()) return null;
        return !empty($className) 
        ? $this->objectHook($this->fetch(PDO::FETCH_CLASS, $className))
        : $this->fetch(PDO::FETCH_OBJ); // \stdClass
    }

    /**
     * Получение всех записей в виде массива объектов.
     * @param string|null имя класса
     * @return array
     */
    public function objectAll(string $className = null): array
    {
        if (0 === $this->rowCount()) return [];
        return $className
        ? $this->objectsHook($this->fetchAll(PDO::FETCH_CLASS, $className))
        : $this->fetchAll(PDO::FETCH_OBJ); // \stdClass
    }

    /**
     * Добавление параметров записи в объект.
     * @param object
     * @return object
     */
    public function intoObject(object &$object): object
    {
        if (0 === $this->rowCount()) return $object;
        $this->fetch(PDO::FETCH_INTO, $object);
        return $this->objectHook($object);
    }


    // Хуки

    /**
     * Хук для постобработки полученного объекта записи.
     * @param object|null запись
     * @return object|null постобработанная запись
     */
    protected function objectHook(object $row = null): ?object
    {
        if (!empty($row)) {
            if ($this->canIdentityMapUpdate()) {
                $this->identityMapUpdate($row);
            }
            if ($this->hasAfterFindMethod($row)) {
                $this->runAfterFindMethod($row);
            }
        }
        return $row;
    }

    /**
     * Хук для постобработки полученных объектов записей.
     * @param array|null записи
     * @return array|null постобработанные записи
     */
    protected function objectsHook(array $rows = null): ?array
    {
        if (!empty($rows) && ($this->canIdentityMapUpdate()
            || $this->hasAfterFindMethod($rows[0])))
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
    protected function hasAfterFindMethod(object &$row): bool
    {
        return method_exists($row, 'afterFind');
    }

    /**
     * Запуска метода afterFind сущности, если есть.
     * @param object сущность
     */
    protected function runAfterFindMethod(object &$row)
    {
        $row->afterFind();
    }

    /**
     * Проверка возможности обновления IdentityMap.
     * @return bool
     */
    protected function canIdentityMapUpdate(): bool
    {
        return method_exists($this->db, 'table') 
            && method_exists($this->db, 'identityMapUpdate');
    }

    /**
     * Хук для обновления сущности в IdentityMap.
     * @param object сущность
     */
    protected function identityMapUpdate(object &$object)
    {
        try {
            $primaryKey = $this->db->table($this->tableName())->primaryKey();
            $object = $this->db->identityMapUpdate($object, $primaryKey);
        } catch (IdentityMapException $e) {
            if ($this->db->isStrictPrimary()) throw $e;
            else return;
        }
    }
}
