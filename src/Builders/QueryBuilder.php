<?php
namespace Evas\Db\Builders;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;

class QueryBuilder implements QueryBuilderInterface
{
    /** @var DatabaseInterface соединение с базой данных */
    public $db;

    /**
     * Конструктор.
     * @param DatabaseInterface
     */
    public function __construct(DatabaseInterface &$db)
    {
        $this->db = $db;
    }


    // Получение данных для выполнения запроса

    /**
     * Получение собранного sql-запроса.
     * @return string
     */
    public function getSql(): string
    {
        return $this->db->grammar()->buildQuery($this);
    }

    /**
     * Получение экранируемых значений собранного запроса.
     * @param string|null для получения части экранируемых значений
     * @return array
     */
    public function getBindings(string $part = null): array
    {
        return [];
    }

    /**
     * Получение sql-запроса и экранируемых значений.
     * @return array [sql, values]
     */
    public function getSqlAndBindings(): array
    {
        return [$this->getSql(), $this->getBindings()];
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
        $this->addBindings('update', $vals);
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
            $sql[] = $this->wrapColumn($key) . ' = ?';
            $vals[] = $this->db->quoteArrayOrObject($val);
        }
        $sql = implode(', ', $sql);
        return $this->updateRaw($sql, $vals);
    }
}
