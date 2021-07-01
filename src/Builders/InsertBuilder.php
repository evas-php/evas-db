<?php
/**
 * Сборщик INSERT-запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Base\Help\PhpHelp;
use Evas\Db\Base\QueryResult;
use Evas\Db\Builders\QueryValuesTrait;
use Evas\Db\Exceptions\InsertBuilderException;
use Evas\Db\Interfaces\DatabaseInterface;

class InsertBuilder
{
    /** Подключаем поддержку работы со значениями запроса. */
    use QueryValuesTrait;

    /** @var string имя таблицы */
    public $tbl;

    /** @var array ключи вставляемых значений записи */
    public $keys;

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
        $this->tbl = &$tbl;
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
     */
    public function row($row)
    {
        assert(is_array($row) || is_object($row));
        if (is_object($row)) $row = get_object_vars($row);
        if (PhpHelp::isAssoc($row)) {
            if (empty($this->keys)) $this->keys(array_keys($row));
            foreach ($this->keys as &$key) {
                $this->bindValue($row[$key] ?? null);
            }
        } else {
            $this->bindValues($row);
        }
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
        $keys = '(`'. implode('`, `', $this->keys) .'`)';
        $quote = '('. implode(', ', array_fill(0, count($this->keys), '?')) .')';
        if ($this->rowCount > 1) {
            $quote = implode(', ', array_fill(0, $this->rowCount, $quote));
        }
        $sql = "INSERT INTO $this->tbl $keys VALUES $quote";
        return $sql;
    }

    /**
     * Выполнение запроса.
     * @return QueryResult
     */
    public function query(): QueryResult
    {
        return $this->db->query($this->getSql(), $this->getValues());
    }
}
