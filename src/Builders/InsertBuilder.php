<?php
/**
 * Сборщик INSERT-запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Base\Help\PhpHelp;
use Evas\Db\Base\QueryResult;
use Evas\Db\Exceptions\InsertBuilderException;
use Evas\Db\Interfaces\DatabaseInterface;

class InsertBuilder
{
    /** @var string имя таблицы */
    public $tbl;

    /** @var array ключи вставляемых значений записи */
    public $keys;

    /** @var array значения для экранирования */
    public $bindings = [];

    /** @var int счетчик количества вставляемых записей */
    protected $rowCount = 0;

    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     * @param string имя таблицы
     */
    public function __construct(DatabaseInterface &$db, string $tbl)
    {
        $this->db = &$db;
        $this->tbl = $tbl;
    }

    /**
     * Установка списка свойств вставляемых в запись.
     * @param array
     * @return self
     */
    public function keys(array $keys)
    {
        $this->keys = &$keys;
        return $this;
    }

    /**
     * Установка значений записи.
     * @param array|object
     * @return self
     * @throws \InvalidArgumentException
     */
    public function row($row)
    {
        if (!is_array($row) && !is_object($row)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an array or an object, %s given',
                __METHOD__, gettype($id)
            ));
        }
        if (is_object($row)) $row = get_object_vars($row);
        if (PhpHelp::isAssoc($row)) {
            if (empty($this->keys)) $this->keys(array_keys($row));
            $row = array_values($row);
        }
        $this->addBindings($row);
        $this->rowCount++;
        return $this;
    }

    /**
     * Установка значений нескольких записей.
     * @param array
     * @return self
     */
    public function rows(array $rows)
    {
        foreach ($rows as &$row) { $this->row($row); }
        return $this;
    }

    /**
     * Получение количества вставляемых записей.
     * @return int
     */
    public function rowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Добавление экранируемых значений записи.
     * @param array значения
     * @return self
     */
    public function addBindings(array $values)
    {
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * Получение экранируемых значений записей.
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Получение собранного sql-запроса.
     * @return string
     * @throws InsertBuilderException
     */
    public function getSql(): string
    {
        if ($this->rowCount == 0) {
            throw new InsertBuilderException('Insert builder rows is empty');
        }
        if (empty($this->keys)) {
            throw new InsertBuilderException('Insert builder keys is empty');
        }
        return $this->db->grammar()->buildInsert($this);
    }

    /**
     * Выполнение запроса.
     * @return QueryResult
     */
    public function query(): QueryResult
    {
        return $this->db->query($this->getSql(), $this->getBindings());
    }
}
