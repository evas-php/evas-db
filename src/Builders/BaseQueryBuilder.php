<?php
/**
 * Базовый сборщик запросов SELECT/UPDATE/DELETE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Db\Builders\ForQueryAndJoinBuildersTrait;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;

class BaseQueryBuilder implements QueryBuilderInterface
{
    /** Подключаем вспомогательные методы для сборщика */
    use ForQueryAndJoinBuildersTrait;

    /** @var array доступные операторы */
    public static $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'LIKE', 'LIKE BINARY', 'NOT LIKE', 'ILIKE',
        '&', '|', '^', '<<', '>>', '&~',
        'RLIKE', 'NOT RLIKE', 'REGEXP', 'NOT REGEXP',
        '~', '~*', '!~', '!~*', 'SIMILAR TO',
        'NOT SIMILAR TO', 'NOT ILIKE', '~~*', '!~~*',
    ];

    /** @var DatabaseInterface соединение с базой данных */
    public $db;

    /** @var array from */
    public $from = '';

    /** @var array забираемые поля */
    public $columns = [];

    /** @var array|bool|null кастройка distinct */
    public $distinct;

    /** @var array where часть */
    public $wheres = [];

    /** @var array поля группировки */
    public $groups = [];

    /** @var array having часть (условие для данных агрегированных group by) */
    public $havings = [];

    /** @var array поля сортировки */
    public $orders = [];

    /** @var array агрегаты */
    public $aggregates = [];

    /** @var int сдвиг поиска */
    public $offset;

    /** @var int лимит выдачи */
    public $limit;

    /** @var array unions */
    public $unions = [];

    /** @var string тип запроса */
    public $type = 'select';

    /** @var array значения для экранирования */
    protected $bindings = [
        'select' => [],
        'update' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'orderBy' => [],
        'union' => [],
    ];

    /** @var array колбэки для единоразового выполнения перед запросом */
    protected $beforeQueryCallbacks = [];

    /**
     * Конструктор.
     * @param DatabaseInterface
     */
    public function __construct(DatabaseInterface &$db)
    {
        $this->db = $db;
    }

    /**
     * Проверка на доступность к подзапросам.
     * @param mixed проверяемая переменная
     * @return bool
     */
    protected function isQueryable($query): bool
    {
        return $query instanceof \Closure || $query instanceof self;
    }

    // ----------
    // WRAPS
    // ----------

    protected function wrapColumn(string $value): string
    {
        return $this->db->grammar()->wrapColumn($value);
    }

    // ----------
    // SUB QUERIES
    // ----------

    /**
     * Создание нового экземпляра сборщика с тем же соединением.
     * @return static
     */
    public function newQuery()
    {
        return new static($this->db);
    }

    /**
     * Создание экземпляра сборщика для подзапроса.
     * @return static
     */
    protected function forSubQuery()
    {
        return $this->newQuery();
    }

    /**
     * Создание нового экземпляра сборщика для подзапроса в той же таблице.
     * @return static
     */
    public function forNestedWhere()
    {
        return $this->forSubQuery()->from($this->from);
    }

    /**
     * Создание подзапроса с получением sql и экранируемых значений.
     * @param \Closure|self|OrmQueryBuilder
     * @return array [sql, bindings]
     * @throws \InvalidArgumentException
     */
    protected function createSub($query): array
    {
        if ($query instanceof \Closure) {
            $cb = $query;
            $cb($query = $this->forSubQuery());
        }
        // if ($query instanceof self || $query instanceof EloquentBuilder || $query instanceof Relation) {
        if ($query instanceof self) {
            $query = $this->changeDbNameIfCrossDatabaseQuery($query);
            return [$query->getSql(), $query->getBindings()];
        } else if (is_string($query)) {
            $query = preg_match('/^\w+$/', $query) ? $this->wrapColumn($query) : "($query)";
            return [$query, []];
        } else {
            throw new \InvalidArgumentException(
                'A subquery must be a query builder instance, a Closure, or a string.'
            );
        }
    }

    /**
     * Смена имени базы для подзапроса к другой базе.
     * @param self|OrmQueryBuilder
     * @return self|OrmQueryBuilder
     */
    protected function changeDbNameIfCrossDatabaseQuery($query)
    {
        if ($query->db->dbname !== $this->db->dbname) {
            $dbname = $query->db->dbname;
            if (strpos($query->from, $dbname) !== 0 && strpos($query->from, '.') === false) {
                $query->from($dbname.'.'.$query->from);
            }
        }
        return $query;
    }


    // ----------
    // WHERES
    // ----------

    /**
     * Добавление where условия.
     * @param string тип where
     * @param array параметры условия
     * @return self
     */
    protected function pushWhere(string $type, array $where)
    {
        $where['type'] = $type;
        $this->wheres[] = $where;
        if (!empty($where['values'])) {
            $this->addBindings('where', $where['values']);
        } else if (isset($where['value'])) {
            $this->addBinding('where', $where['value']);
        }
        return $this;
    }

    // ----------
    // GROUP BY
    // ----------

    /**
     * Установка группировки.
     * @param string ...группы
     * @return self
     */
    public function groupBy(string ...$groups) {
        $this->groups = [];
        return $this->addGroupBy(...$groups);
    }

    /**
     * Добавление группировки.
     * @param string ...группы
     * @return self
     */
    public function addGroupBy(string ...$groups) {
        foreach ($groups as $group) {
            $this->groups[] = $this->wrapColumn($group);
        }
        return $this;
    }

    /**
     * Установка группировки sql-строкой.
     * @param string sql
     * @param array|null экранируемые значения
     * @return self
     */
    public function groupByRaw(string $sql, array $values = [])
    {
        $this->groups = [];
        return $this->addGroupByRaw($sql, $values);
    }

    /**
     * Добавление группировки sql-строкой.
     * @param string sql
     * @param array|null экранируемые значения
     * @return self
     */
    public function addGroupByRaw(string $sql, array $values = [])
    {
        $this->groups[] = $sql;
        if ($values) $this->addBindings('groupBy', $values);
        return $this;
    }


    // ----------
    // HAVING
    // ----------

    /**
     * Добавление having условия.
     * @param string тип having
     * @param array параметры условия
     * @return self
     */
    protected function pushHaving(string $type, array $having)
    {
        $having['type'] = $type;
        $this->havings[] = $having;
        if (!empty($having['values'])) {
            $this->addBindings('having', $having['values']);
        } else if (isset($having['value'])) {
            $this->addBinding('having', $having['value']);
        }
        return $this;
    }


    // ----------
    // ORDERING
    // ----------

    /**
     * Установка сортировки sql-строкой.
     * @param string sql
     * @param array|null экранируемые значения
     * @return self
     */
    public function orderByRaw(string $sql, array $values = [])
    {
        $this->orders[] = $sql;
        if ($values) $this->addBindings('orderBy', $values);
        return $this;
    }

    /**
     * Установка сортировки.
     * @param array|string|\Closure|self столбцы
     * @param bool|null сортировать по убыванию
     * @return self
     */
    public function orderBy($column, bool $isDesc = false)
    {
        if (is_array($column)) {
            foreach ($column as $col => $subDesc) {
                if (is_numeric($col) && is_string($subDesc)) {
                    $col = $subDesc;
                    $subDesc = $isDesc;
                }
                $this->orderBy($col, $subDesc);
            }
        } else if ($this->isQueryable($column)) {
            [$sql, $bindings] = $this->createSub($column);
            $this->orders[] = [$sql, $isDesc];
            $this->addBindings('orderBy', $bindings);
        } else if (is_string($column)) {
            $this->orders[] = [$this->wrapColumn($column), $isDesc];
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an array, a string or a Queryable, %s given',
                __METHOD__, gettype($id)
            ));
        }
        return $this;
    }

    /**
     * Установка сортировки по убыванию.
     * @param array|string|\Closure|self столбцы
     * @return self
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, true);
    }


    // ----------
    // Limit (take) & Offset (skip)
    // ----------

    /**
     * Установка лимита.
     * @param int|null лимит или null для сброса
     * @param int|null сдвиг или null для сброса
     * @return self
     */
    public function limit(int $limit = null, int $offset = null)
    {
        if ($limit < 1) $limit = null;
        $this->limit = $limit;
        return func_num_args() > 1 ? $this->offset($offset) : $this;
    }

    /**
     * Установка сдвига.
     * @param int|null сдвиг или null для сброса
     * @return self
     */
    public function offset(int $offset = null)
    {
        if ($offset < 1) $offset = null;
        $this->offset = $offset;
        return $this;
    }

    /**
     * Установка лимита.
     * @param int|null лимит или null для сброса
     * @param int|null сдвиг или null для сброса
     * @return self
     */
    public function take(int $take = null, int $skip = null)
    {
        return $this->limit(...func_get_args());
    }

    /**
     * Установка сдвига.
     * @param int|null сдвиг или null для сброса
     * @return self
     */
    public function skip(int $offset = null)
    {
        return $this->offset($offset);
    }

    // ----------
    // FINISH
    // ----------

    /**
     * Сборка WHERE
     */
    // public function buildWheres(): string
    // {
    //     return $this->db->grammar()->buildWheres($this->wheres);
    // }

    /**
     * Сборка HAVING
     */
    // public function buildHavings(): string
    // {
    //     return $this->db->grammar()->buildWheres($this->havings);
    // }

    /**
     * Получение первичного ключа таблицы.
     * @return string
     */
    protected function primaryKey(): string
    {
        if (strpos($this->from, ' ') !== false) return 'id';
        return $this->db->table($this->from)->primaryKey();
    }


    /**
     * Получение собранного sql-запроса.
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getSql(): string
    {
        return $this->db->grammar()->buildQuery($this);
    }

    /** @static array приоритет получение экранируемых значений */
    const BINDINGS_PRIORITY = [
        'join', 'where', 'groupBy', 'having', 'orderBy'
    ];

    /**
     * Получение экранируемых значений собранного запроса.
     * @param string|null для получения части экранируемых значений
     * @return array
     */
    public function getBindings(string $part = null): array
    {
        if ($part) return $this->bindings[$part] ?? [];
        $values = [];
        if ('update' === $this->type) {
            $values = array_merge(
                $this->bindings['update'],
                $this->bindings['from']
            );
        } else if ('delete' === $this->type) {
            $values = $this->bindings['from'];
        } else {
            $values = array_merge(
                $this->bindings['select'],
                $this->bindings['from']
            );
        }
        foreach (static::BINDINGS_PRIORITY as $key) {
            if (!empty($this->bindings[$key])) {
                $values = array_merge($values, $this->bindings[$key]);
            }
        }
        return $values;
    }

    /**
     * Получение sql-запроса и экранируемых значений.
     * @return array [sql, values]
     */
    public function getSqlAndBindings(): array
    {
        return [$this->getSql(), $this->getBindings()];
    }

    /**
     * Выполнение sql-запроса.
     * @return QueryResultInterface|object|array of objects
     */
    public function query()
    {
        $this->applyBeforeQueryCallbacks();
        $result = $this->db->query($this->getSql(), $this->getBindings());
        // // пытаемся достать класс модели
        // if ($this->model) {
        //     $model = $this->model;
        // } elseif (method_exists($this->db, 'isModelsInResultUsed') && 
        //     $this->db->isModelsInResultUsed() && !empty($this->tbl)) {
        //     $model = $this->db->findTableModel($this->tbl);
        // }
        // // если класс модели найден, возвращаем объект модели вместо результата
        // if (!empty($model)) {
        //     return 1 === $limit ? $result->classObject($model)
        //         : $result->classObjectAll($model);
        // }
        return $result;
    }

    /**
     * Выполнение select-запроса с получением результирующих строк.
     * @param array|null столбцы
     * @return array
     */
    public function get($columns = null): ?array
    {
        if ($columns) $this->select(...func_get_args());
        return $this->query()->assocArrayAll();
    }

    /**
     * Получение одной записи.
     * @return array
     */
    public function one(): ?array
    {
        return $this->limit(1)->query()->assocArray();
    }

    /**
     * Поиск записи/записей по первичному ключу.
     * @param array|numeric|string значение первичного ключа
     * @param array|null запрашиваемые поля
     * @return array
     * @throws \InvalidArgumentException
     */
    public function find($id, array $columns = ['*'])
    {
        $this->select($columns);
        $primaryKey = $this->primaryKey();
        if (is_array($id)) {
            return $this->whereIn($primaryKey, $id)->get();
        } else if (is_numeric($id) || is_string($id)) {
            return $this->where($primaryKey, $id)->one();
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an array, a numeric or a string, %s given',
                __METHOD__, gettype($id)
            ));
        }
    }

    /**
     * Вызов удаления записи/записей.
     * @param mixed|null id записи/записей
     * @return QueryResult
     */
    public function delete($id = null)
    {
        $this->type = 'delete';
        if ($id) {
            $primaryKey = $this->primaryKey();
            $this->wheres = [];
            $this->bindings['where'] = [];
            if (is_array($id)) {
                $this->whereIn($primaryKey, $id)->limit(count($id));
            } else if (is_numeric($id) || is_string($id)) {
                $this->where($primaryKey, $id)->limit(1);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Argument 1 passed to %s() must be an array, a numeric or a string, %s given',
                    __METHOD__, gettype($id)
                ));
            }
        }
        return $this->query();
    }

    /**
     * Вызов обновления записи/записей sql-строкой.
     * @param string таблицы
     * @param string sql-запрос
     * @param array обновлённые данные
     * @return QueryResult
     */
    public function updateRaw(string $sql, array $vals)
    {
        $this->type = 'update';
        $this->updateSql = $sql;
        $this->addBindings('update', $vals);
        return $this->query();
    }

    /**
     * Вызов обновления записи/записей.
     * @param array обновлённые данные
     * @return QueryResult
     */
    public function update(array $data)
    {
        $vals = [];
        $sql = [];
        foreach ($data as $key => $val) {
            $sql[] = $this->wrapColumn($key) . ' = ?';
            $vals[] = $this->db->quoteArrayOrObject($val);
        }
        $sql = implode(', ', $sql);
        return $this->updateRaw($sql, $vals);
    }


    // ----------
    // HOOKS
    // ----------

    public function beforeQuery(\Closure $callback)
    {
        $this->beforeQueryCallbacks[] = $callback;
        return $this;
    }

    public function applyBeforeQueryCallbacks()
    {
        foreach ($this->beforeQueryCallbacks as $callback) {
            $callback($this);
        }
        $this->beforeQueryCallbacks = [];
    }
}
