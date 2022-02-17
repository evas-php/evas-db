<?php
/**
 * Абстрактный класс для Query и Join сборки.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Base\Help\PhpHelp;
use Evas\Db\Builders\BaseQueryBuilder;

abstract class AbsractQueryBuilder
{

    /**
     * Проверка на доступность к подзапросам.
     * @param mixed проверяемая переменная
     * @return bool
     */
    protected function isQueryable($query): bool
    {
        return $query instanceof \Closure || $query instanceof BaseQueryBuilder;
    }

    // ----------
    // WRAPS
    // ----------

    /**
     * Пробрасываем обёртку имени таблицы из грамматики.
     * @param string имя таблицы
     * @return string обёрнутое имя таблицы
     */
    protected function unwrapTable(string $value): string
    {
        return $this->db->grammar()->unwrapTable($value);
    }

    /**
     * Пробрасываем сброс обёртки имени таблицы из грамматики.
     * @param string обёрнутое имя таблицы
     * @return string имя таблицы без обёртки
     */
    protected function wrapTable(string $value): string
    {
        return $this->db->grammar()->wrapTable($value);
    }

    /**
     * Проброс оборачивания столбца из грамматики.
     * @param string столбец
     * @return string обёрнутый столбец
     */
    protected function wrapColumn(string $value): string
    {
        return $this->db->grammar()->wrapColumn($value);
    }

    // ----------
    // Bindings
    // ----------

    /**
     * Добавление экранируемого значения.
     * @param string назначение значения
     * @param mixed значение
     * @return self
     */
    protected function addBinding(string $type, $value)
    {
        $this->bindings[$type][] = $value;
        return $this;
    }

    /**
     * Добавление экранируемых значений.
     * @param string назначение значения
     * @param array массив значений
     * @return self
     */
    public function addBindings(string $type, array $values)
    {
        $this->bindings[$type] = array_merge($this->bindings[$type] ?? [], array_values($values));
        return $this;
    }

    // ----------
    // Value & Operator Helpers
    // ----------

    /**
     * Подготовка значения и оператора.
     * Для методов, в которых можно опутить оператор сравения - "=".
     * @param mixed значение
     * @param mixed оператор
     * @param bool|null использовать ли оператор в качестве значения
     * @return array [значение, оператор]
     * @throws \InvalidArgumentException
     */
    protected function prepareValueAndOperator($value, $operator, bool $useDefault = false): array
    {
        if ($useDefault) {
            return [$operator, '='];
        } else if ($this->invalidValueAndOperator($value, $operator)) {
            throw new \InvalidArgumentException(json_encode([
                'error' => 'Illegal operator and value combination.',
                'operator' => $operator,
                'value' => $value,
            ]));
        }
        return [$value, $operator];
    }

    /**
     * Проверка на неправильность значения и оператора.
     * @param mixed значение
     * @param mixed оператор
     * @return bool
     */
    protected function invalidValueAndOperator($value, $operator): bool
    {
        /**
         * @todo Проверять операторы конкретной СУБД
         */
        return is_null($value) && in_array($operator, static::$operators) 
        && !in_array($operator, ['=', '<>', '!=']);
    }

    // ----------
    // SUB QUERIES
    // ----------

    /**
     * Создание нового экземпляра сборщика с тем же соединением.
     * @return BaseQueryBuilder
     */
    public function newQuery(): BaseQueryBuilder
    {
        return $this->db->newQueryBuilder();
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
     * Создание подзапроса с получением sql и экранируемых значений.
     * @param \Closure|self
     * @return array [sql, bindings]
     * @throws \InvalidArgumentException
     */
    protected function createSub($query): array
    {
        if ($query instanceof \Closure) {
            $cb = $query;
            $cb($query = $this->forSubQuery());
        }
        if ($query instanceof BaseQueryBuilder) {
            $query = $this->changeDbNameIfCrossDatabaseQuery($query);
            return ['(' . $query->getSql() . ')', $query->getBindings()];
        } else if (is_string($query)) {
            $query = preg_match('/^\w+(\.\w+)?$/u', $query) ? $this->wrapColumn($query) : "($query)";
            return [$query, []];
        } else {
            throw new \InvalidArgumentException(sprintf(
                'A subquery must be a query builder instance, a Closure, or a string, %s given',
                PhpHelp::getType($query)
            ));
        }
    }

    /**
     * Смена имени базы для подзапроса к другой базе.
     * @param self
     * @return self
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
    // FROM
    // ----------

    /**
     * Установка from sql-сторокой.
     * @param string $sql
     * @param array|null значения для экранирования
     * @return self
     */
    public function fromRaw(string $sql, array $values = [])
    {
        $this->from = $sql;
        return $this->addBindings('from', $values);
    }

    /**
     * Установка from таблицей или sql-подзапросом.
     * @param string|\Closure|self|OrmQueryBuilder
     * @param string|null псевдоним
     * @return self
     */
    public function from($table, string $as = null)
    {
        if ($this->isQueryable($table) && !is_null($as)) {
            return $this->fromSub($table, $as);
        }
        $table = $this->wrapTable($table);
        // $table = $this->db->grammar()->unwrapTable($table);
        // $table = (preg_match('/^\w+(\.\w+)?$/u', $table)) ? $this->wrapTable($table) : "($table)";
        $this->from = $as ? ("$table AS " . $this->wrapTable($as)) : $table;
        return $this;
    }

    /**
     * Установка from sql-подзапросом.
     * @param \Closure|self|OrmQueryBuilder
     * @param string псевдоним
     * @return self
     */
    public function fromSub($query, string $as)
    {
        [$sql, $bindings] = $this->createSub($query);
        // $sql = $this->unwrapTable($join->from);
        // $sql = (preg_match('/^\w+$/u', $sql)) ? $this->wrapTable($sql) : "($sql)";
        return $this->fromRaw(
            "$sql AS " . $this->wrapTable($as), $bindings
        );
    }
}
