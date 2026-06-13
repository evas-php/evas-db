<?php
/**
 * Результат запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryResultInterface;

class QueryResult implements QueryResultInterface
{
    /** @var \PDOStatement */
    protected $stmt;

    /** @var DatabaseInterface */
    protected $db;

    /**
     * Конструктор.
     * @param \PDOStatement
     * @param DatabaseInterface
     */
    public function __construct(\PDOStatement &$stmt, DatabaseInterface &$db)
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

    
    // Мета-информация

    /**
     * Получение statement ответа базы.
     * @return \PDOStatement
     */
    public function stmt(): \PDOStatement
    {
        return $this->stmt;
    }

    /**
     * Получение имени таблицы select-запроса.
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


    // Вспомогательные методы для получения результата

    /**
     * Реальный fetch или fetchAll с поддержкой мода.
     * @param bool|null исользовать ли fetchAll вместо fetch
     * @param int|null модификатор
     * @param mixed|null уточнение модификатора
     * @return mixed|null данные записи
     * @throws DbException
     */
    protected function realFetch(bool $all = false, int $mode = null, $modeMore = null)
    {
        if (!empty($modeMore) && !empty($mode)) {
            $this->stmt->setFetchMode($mode, $modeMore);
            $mode = null;
        }
        $method = $all ? 'fetchAll' : 'fetch';
        $result = $this->stmt->$method($mode);
        if ($all && empty($result) || false === $result) {
            throw new DbException(
                strpos($this->stmt->queryString, 'INSERT') !== false
                ? 'Insert query returns no rows'
                : 'Failed to get data from previous sql query due to buffer overwriting'
            );
        }
        return $result;
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
        return $this->rowCount() < 1 ? null
        : $this->realFetch(false, $mode, $modeMore);
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
        return $this->rowCount() < 1 ? []
        : $this->realFetch(true, $mode, $modeMore);
    }


    // Получение записи/записей в разном виде

    /**
     * Получение записи в виде нумерованного массива.
     * @return numericArray|null
     */
    public function numericArray(): ?array
    {
        return $this->fetch(\PDO::FETCH_NUM);
    }

    /**
     * Получение всех записей в виде массива нумерованных массивов.
     * @return array
     */
    public function numericArrayAll(): array
    {
        return $this->fetchAll(\PDO::FETCH_NUM);
    }

    /**
     * Получение записи в виде ассоциативного массива.
     * @return assocArray|null
     */
    public function assocArray(): ?array
    {
        return $this->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Получение всех записей в виде массива ассоциативных массивов.
     * @return array
     */
    public function assocArrayAll(): array
    {
        return $this->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Получение записи в виде объекта.
     * @param string|null имя класса, если он должен отличаться от stdClass
     * @return stdClass|null
     */
    public function object(string $className = null): ?object
    {
        return 1 > $this->rowCount() ? null
        : (!empty($className) 
            ? $this->objectHook($this->fetch(\PDO::FETCH_CLASS, $className))
            : $this->fetch(\PDO::FETCH_OBJ) // \stdClass
        );
    }

    /**
     * Получение всех записей в виде массива объектов.
     * @param string|null имя класса, если он должен отличаться от stdClass
     * @return array
     */
    public function objectAll(string $className = null): array
    {
        return 1 > $this->rowCount() ? []
        : (!empty($className) 
            ? $this->objectsHook($this->fetchAll(\PDO::FETCH_CLASS, $className))
            : $this->fetchAll(\PDO::FETCH_OBJ) // \stdClass
        );
    }

    /**
     * Добавление параметров записи в объект.
     * @param object
     * @return object
     */
    public function intoObject(object &$object): object
    {
        if (1 > $this->rowCount()) return $object;
        $this->fetch(\PDO::FETCH_INTO, $object);
        return $this->objectHook($object);
    }

    
    // Хуки

    protected function objectHook(object $row = null): ?object
    {
        return $row;
    }

    protected function objectsHook(array $rows = null): ?array
    {
        return $rows;
    }
}
