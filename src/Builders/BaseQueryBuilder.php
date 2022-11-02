<?php
/**
 * Базовый сборщик запросов SELECT/UPDATE/DELETE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Db\Builders\AbstractQueryBuilder;
use Evas\Db\Interfaces\DatabaseInterface;

class BaseQueryBuilder extends AbstractQueryBuilder
{    
    /** @var DatabaseInterface соединение с базой данных */
    protected $db;

    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     * @param string|null имя таблицы
     */
    public function __construct(DatabaseInterface &$db, string $table = null)
    {
        $this->db = $db;
        if (!is_null($table)) $this->from($table);
    }


    // Получение данных для выполнения запроса

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
     * @return string
     */
    public function getSql(): string
    {
        return $this->db->grammar()->buildQuery($this);
    }

    // Выполнение запроса

    /**
     * Выполнение sql-запроса.
     * @return QueryResultInterface|object|array of objects
     */
    public function query() //: QueryResultInterface
    {
        return $this->db->query($this->getSql(), $this->getBindings());
    }

    /**
     * Выполнение select-запроса с получением нескольких записей.
     * @param array|null столбцы для получения
     * @return array найденные записи
     */
    public function get($columns = null): array
    {
        if ($columns) $this->addSelect(...func_get_args());
        return $this->query()->assocArrayAll();
    }

     /**
     * Выполнение select-запроса с получением одной записи.
     * @param array|null столбцы для получения
     * @return array|null найденная запись
     */
    public function one($columns = null)
    {
        if ($columns) $this->addSelect(...func_get_args());
        return $this->limit(1)->query()->assocArray();
    }

    /**
     * Поиск записи/записей по первичному ключу.
     * @param array|numeric|string значение первичного ключа
     * @param array|null запрашиваемые поля
     * @return array найденная или найденные записи
     * @throws \InvalidArgumentException
     */
    public function find($id, array $columns = ['*'])
    {
        $this->select($columns);
        $primaryKey = $this->primaryKey();
        if (is_array($id))
            return $this->whereIn($primaryKey, $id)->get();
        else if (is_numeric($id) || is_string($id))
            return $this->where($primaryKey, $id)->one();
        else throw new \InvalidArgumentException(sprintf(
            'Argument 1 passed to %s() must be an array, a numeric or a string, %s given',
            __METHOD__, gettype($id)
        ));
    }

    /**
     * Выолнение delete-запроса удаления записи/записей.
     * @param mixed|null id записи/записей, если нужно удалить конкретные
     * @return QueryResultInterface
     * @throws \InvalidArgumentException
     */
    public function delete($id = null) //: QueryResultInterface
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
     * Выолнение update-запроса обновления записи/записей sql-строкой.
     * @param string sql-запрос
     * @param array обновлённые данные
     * @return QueryResultInterface
     */
    public function updateRaw(string $sql, array $vals) //: QueryResultInterface
    {
        $this->type = 'update';
        $this->updateSql = $sql;
        $this->addBindings('update', array_values($vals));
        return $this->query();
    }

    /**
     * Выолнение update-запроса обновления записи/записей.
     * @param array обновлённые данные
     * @return QueryResultInterface
     */
    public function update(array $data) //: QueryResultInterface
    {
        $vals = [];
        $sql = [];
        foreach ($data as $key => $val) {
            $sql[] = $this->wrap($key) . ' = ?';
            $vals[] = $this->db->quoteArrayOrObject($val);
        }
        $sql = implode(', ', $sql);
        return $this->updateRaw($sql, $vals);
    }
}
