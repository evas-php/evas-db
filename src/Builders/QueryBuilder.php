<?php
/**
 * Сборщик запроса SELECT/UPDATE/DELETE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Db\Builders\JoinBuilder;
use Evas\Db\Builders\QueryValuesTrait;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\JoinBuilderInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;
use Evas\Db\Interfaces\QueryResultInterface;

class QueryBuilder implements QueryBuilderInterface
{
    /** Подключаем поддержку работы со значениями запроса. */
    use QueryValuesTrait;

    /** @var DatabaseInterface соединение с базой данных */
    public $db;

    /** @var string начало запроса и from */
    public $from;

    /** @var array джоины */
    public $join = [];

    /** @var string where часть */
    public $where;

    /** @var string поля группировки */
    public $groupBy;

    /** @var string having часть (условие для данных агрегированных group by) */
    public $having;

    /** @var string поля сортировки */
    public $orderBy;

    /** @var bool сортировать по убыванию */
    public $orderDesc = false;

    /** @var int сдвиг поиска */
    public $offset;

    /** @var int лимит выдачи */
    public $limit;

    /** @var string класс модели */
    public $model;

    /**
     * Конструктор.
     * @param DatabaseInterface
     */
    public function __construct(DatabaseInterface &$db, string $model = null)
    {
        $this->db = $db;
        $this->model = $model;
    }

    /**
     * Начало SELECT запроса.
     * @param string имя таблицы
     * @param string поля
     * @return self
     */
    public function select(string $tbl, string $columns = null): QueryBuilderInterface
    {
        if (empty($columns)) $columns = '*';
        $this->tbl = trim($tbl, '`');
        return $this->from("SELECT $columns FROM `$this->tbl`");
    }

    /**
     * Начало DELETE запроса.
     * @param string имя таблицы
     * @return self
     */
    public function delete(string $tbl): QueryBuilderInterface
    {
        $tbl = trim($tbl, '`');
        return $this->from("DELETE FROM `$tbl`");
    }

    /**
     * Начало UPDATE запроса.
     * @param string имя таблицы
     * @param string|array|object значения записи или sql-запрос
     * @param array|null значения для экранирования используемые в sql-запросе
     * @return self
     */
    public function update(string $tbl, $row, array $vals = []): QueryBuilderInterface
    {
        assert(is_array($row) || is_object($row) || is_string($row));
        if (is_array($row) || is_object($row)) { 
            $upd = [];
            foreach ($row as $key => $val) {
                $upd[] = "`$key` = ?";
                $vals[] = $val;
            }
            $upd = implode(', ', $upd);
        } else {
            $upd = $row;
        }
        $this->tbl = trim($tbl, '`');
        return $this->from("UPDATE `$this->tbl` SET $upd", $vals);
    }


    /**
     * Установка части FROM.
     * @param string часть from
     * @param array|null параметры для экранирования
     * @return self
     */
    public function from(string $from, array $values = []): QueryBuilderInterface
    {
        $this->from = $from;
        return $this->bindValues($values);
    }

    /**
     * Запуск сборщика INNER JOIN.
     * @param string|null имя таблицы
     * @return JoinBuilder
     */
    public function innerJoin(string $tbl = null): JoinBuilderInterface
    {
        $this->tbl = null;
        return new JoinBuilder($this, 'INNER', $tbl);
    }

    /**
     * Запуск сборщика LEFT JOIN.
     * @param string|null имя таблицы
     * @return JoinBuilder
     */
    public function leftJoin(string $tbl = null): JoinBuilderInterface
    {
        $this->tbl = null;
        return new JoinBuilder($this, 'LEFT', $tbl);
    }

    /**
     * Запуск сборщика RIGHT JOIN.
     * @param string|null имя таблицы
     * @return JoinBuilder
     */
    public function rightJoin(string $tbl = null): JoinBuilderInterface
    {
        $this->tbl = null;
        return new JoinBuilder($this, 'RIGHT', $tbl);
    }

    /**
     * Запуск сборщика OUTER JOIN.
     * @param string|null имя таблицы
     * @return JoinBuilder
     */
    public function outerJoin(string $tbl = null): JoinBuilderInterface
    {
        $this->tbl = null;
        return new JoinBuilder($this, 'OUTER', $tbl);
    }

    /**
     * Запуск сборщика INNER JOIN (алиас для innerJoin).
     * @param string|null имя таблицы
     * @return JoinBuilder
     */
    public function join(string $tbl = null): JoinBuilderInterface
    {
        return $this->innerJoin($tbl);
    }

    /**
     * Добавление JOIN с помощью sql.
     * @param string join sql
     * @param array параметры для экранирования
     * @return self
     */
    public function setJoin(string $join, array $values = []): QueryBuilderInterface
    {
        $this->join[] = $join;
        return $this->bindValues($values);
    }


    /**
     * Установка WHERE.
     * @param string where часть
     * @param array параметры для экранирования
     * @return self
     */
    public function where(string $where, array $values = []): QueryBuilderInterface
    {
        if (!empty($this->where)) $this->where .= ' ';
        $this->where .= $where;
        return $this->bindValues($values);
    }

    /**
     * Установка WHERE IN.
     * @param string имя поля
     * @param array массив значений сопоставления
     * @return self
     */
    public function whereIn(string $key, array $values): QueryBuilderInterface
    {
        if (!empty($this->where)) $this->where .= ' ';
        $quote = implode(',', array_fill(0, count($values), '?'));
        $this->where .= "$key IN ($quote)";
        return $this->bindValues($values);
    }


    /**
     * Установка GROUP BY.
     * @param string столбцы группировки
     * @param string|null having условие
     * @param array|null параметра having для экранирования
     * @return self
     */
    public function groupBy(string $columns, string $having = null, array $havingValues = []): QueryBuilderInterface
    {
        $this->groupBy = $columns;
        return !empty($having) ? $this->having($having, $havingValues) : $this;
    }

    /**
     * Установка HAVING.
     * @param string having условие
     * @param array параметры для экранирования
     * @return self
     */
    public function having(string $having, array $values = []): QueryBuilderInterface
    {
        $this->having = $having;
        return $this->bindValues($values);
    }

    /**
     * Установка ORDER BY.
     * @param string столбцы сортировки
     * @param bool|null сортировать по убыванию
     * @return self
     */
    public function orderBy(string $columns, bool $desc = false): QueryBuilderInterface
    {
        $this->orderBy = $columns;
        $this->orderDesc = $desc;
        return $this;
    }

    /**
     * Установка OFFSET.
     * @param int сдвиг
     * @return self
     */
    public function offset(int $offset): QueryBuilderInterface
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Установка LIMIT.
     * @param int лимит
     * @param int|null сдвиг
     * @return self
     */
    public function limit(int $limit, int $offset = null): QueryBuilderInterface
    {
        $this->limit = $limit;
        return $offset !== null ? $this->offset($offset) : $this;
    }


    /**
     * Получение sql.
     * @return string
     */
    public function getSql(): string
    {
        $sql = $this->from;
        if (!empty($this->join)) {
            $sql .= ' ' . implode(' ', $this->join);
        }
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->where;
        }
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . $this->groupBy;
            if (!empty($this->having)) {
                $sql .= ' HAVING ' . $this->having;
            }
        }
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . $this->orderBy;
            if (true === $this->orderDesc) {
                $sql .= ' DESC';
            }
        }
        if (!empty($this->limit)) {
            $sql .= ' LIMIT ' . $this->limit; 
        }
        if (!empty($this->offset)) {
            $sql .= ' OFFSET ' . $this->offset;
        }
        return $sql;
    }

    /**
     * Получение одной записи.
     * @return QueryResultInterface|object
     */
    public function one(): object
    {
        return $this->query(1);
    }

    /**
     * Выполнение запроса.
     * @param int|null limit
     * @param int|null offset
     * @return QueryResultInterface|object|array of objects
     */
    public function query(int $limit = null, int $offset = null): object
    {
        if ($limit > 0) $this->limit($limit);
        if ($offset > 0) $this->offset($offset);
        $result = $this->db->query($this->getSql(), $this->values());

        // пытаемся достать класс модели
        if ($this->model) {
            $model = $this->model;
        } elseif (method_exists($this->db, 'isModelsInResultUsed') && 
            $this->db->isModelsInResultUsed() && !empty($this->tbl)) {
            $model = $this->db->findTableModel($this->tbl);
        }
        // если класс модели найден, возвращаем объект модели вместо результата
        if (!empty($model)) {
            return 1 === $limit ? $result->classObject($model)
                : $result->classObjectAll($model);
        }
        return $result;
    }
}
