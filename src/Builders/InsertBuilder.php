<?php
/**
 * Сборщик INSERT-запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Base\Help\PhpHelp;
use Evas\Db\Exceptions\InsertBuilderException;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\InsertBuilderInterface;
use Evas\Db\QueryResult;

class InsertBuilder implements InsertBuilderInterface
{
    /** @var string имя таблицы */
    public $table;

    /** @var array столбцы вставляемых значений записи */
    public $columns;

    /** @var array значения для экранирования */
    protected $bindings = [];

    /** @var int счетчик количества вставляемых записей */
    protected $rowCount = 0;

    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     * @param string имя таблицы
     */
    public function __construct(DatabaseInterface &$db, string $table)
    {
        $this->db = &$db;
        $this->table = $table;
    }


    // Настройка запроса

    /**
     * Установка столбцов вставляемых значений записи.
     * @param array столбцы
     * @return self
     */
    public function columns(array $columns): InsertBuilder
    {
        $this->columns = &$columns;
        return $this;
    }

    /**
     * Установка значений записи.
     * @param array|object значения записи
     * @return self
     * @throws \InvalidArgumentException
     */
    public function row($row): InsertBuilder
    {
        if (!is_array($row) && !is_object($row)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an array or an object, %s given',
                __METHOD__, gettype($id)
            ));
        }
        if (is_object($row)) $row = get_object_vars($row);
        if (PhpHelp::isAssoc($row)) {
            if (empty($this->columns)) $this->columns(array_columns($row));
            $row = array_values($row);
        }
        $this->addBindings($row);
        $this->rowCount++;
        return $this;
    }

    /**
     * Установка значений нескольких записей.
     * @param array значения записей
     * @return self
     */
    public function rows(array $rows): InsertBuilder
    {
        foreach ($rows as &$row) { $this->row($row); }
        return $this;
    }

    /**
     * Добавление экранируемых значений записи.
     * @param array значения
     * @return self
     */
    public function addBindings(array $values): InsertBuilder
    {
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }


    // Получение данных для выполнения запроса

    /**
     * Получение собранного sql-запроса.
     * @return string
     * @throws InsertBuilderException
     */
    public function getSql(): string
    {
        if (empty($this->rowCount)) {
            throw new InsertBuilderException('Insert builder rows is empty');
        }
        if (empty($this->columns)) {
            throw new InsertBuilderException('Insert builder columns is empty');
        }
        return $this->db->grammar()->buildInsert($this);
    }

    /**
     * Получение количества вставляемых записей.
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Получение экранируемых значений собранного запроса.
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }


    // Выполнение запроса

    /**
     * Выполнение запроса.
     * @return QueryResult
     */
    public function query(): QueryResult
    {
        return $this->db->query($this->getSql(), $this->getBindings());
    }
}
