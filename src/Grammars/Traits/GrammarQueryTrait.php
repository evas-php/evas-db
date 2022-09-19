<?php
/**
 * Трейт грамматики запросов СУБД.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars\Traits;

use Evas\Db\Interfaces\InsertBuilderInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;

trait GrammarQueryTrait
{
    /**
     * Сборка insert запроса.
     * @param InsertBuilderInterface
     * @return string готовый insert запрос
     */
    public function buildInsert(InsertBuilderInterface &$builder): string
    {
        $columns = "({$this->wrapColumns($builder->columns)})";
        $quote = '('. implode(', ', array_fill(0, count($builder->columns), '?')) .')';
        if ($builder->getRowCount() > 1) {
            $quote = implode(', ', array_fill(0, $builder->getRowCount(), $quote));
        }
        $sql = "INSERT INTO {$this->wrapTable($builder->table)} $columns VALUES $quote";
        return $sql;
    }

    /**
     * Сборка select/update/delete запроса.
     * @param QueryBuilderInterface
     * @return string готовый select запрос
     * @throws \InvalidArgumentException
     */
    public function buildQuery(QueryBuilderInterface &$builder): string
    {
        if (empty($builder->from)) {
            throw new \InvalidArgumentException('Not has table name in QueryBuilder');
        }
        if ('update' === $builder->type) {
            $sql = "UPDATE {$builder->from} SET {$builder->updateSql}";
        } else if ('delete' === $builder->type) {
            $sql = "DELETE FROM {$builder->from}";
        } else {
            $builder->type = 'select';
            $cols = $builder->aggregates;
            if (empty($cols)) $cols = $builder->columns;
            if (empty($cols)) $cols = ['*'];
            $cols = implode(', ', $cols);
            $sql = "SELECT {$cols} FROM {$builder->from}";
        }
        if (count($builder->joins)) $sql .= $this->buildJoins($builder->joins);
        if (count($builder->wheres)) {
            $where = $this->buildWheres($builder->wheres);
            if ($where) $sql .= " WHERE {$where}";
        }
        if ('select' == $builder->type) {
            if (count($builder->groups)) $sql .= $this->buildGroups($builder->groups);
            if (count($builder->havings)) {
                $having = $this->buildWheres($builder->havings);
                if ($having) $sql .= " HAVING {$having}";
            }
        }
        if (count($builder->unions)) $sql .= $this->buildUnions($builder);
        if (count($builder->orders)) $sql .= $this->buildOrders($builder->orders);
        if (!empty($builder->limit)) $sql .= $this->buildLimit($builder->limit);
        if (!empty($builder->offset)) $sql .= $this->buildOffset($builder->offset);
        return $sql;
    }


    // Частичная сборка select-запроса

    /**
     * Сборка Where или Having части sql.
     * @param array части where или having
     * @return string готовый sql-where или sql-having
     */
    protected function buildWheres(array &$wheres): string
    {
        $sql = '';
        foreach ($wheres as $i => &$where) {
            $method = 'buildWhere' . $where['type'];
            if (method_exists($this, $method)) {
                $line = $this->$method($where);
                if ($line) {
                    if ($i > 0) $sql .= $this->getWhereSeparator($where);
                    $sql .= $line;
                }
            }
        }
        return $sql;
    }

    /**
     * Получение разделителя между wheres.
     * @param array where
     * @return string
     */
    protected function getWhereSeparator(array $where): string
    {
        return ($where['isOr'] ?? null) ? ' OR ' : ' AND ';
    }

    /**
     * Получение NOT для условия по необходимости.
     * @param array where
     * @return string
     */
    protected function getNot(array $where): string
    {
        return ($where['isNot'] ?? null) ? 'NOT ' : '';
    }

    /**
     * Сборка join.
     * @param 
     * @return string готовый sql-join
     */
    protected function buildJoin($join): string
    {}

    /**
     * Сборка нескольких join.
     * @param 
     * @return string готовые sql-join
     */
    protected function buildJoins(array $joins): string
    {}

    /**
     * Сборка группировки.
     * @param array группировки
     * @return string готовый sql-group
     */
    protected function buildGroups(array $groups): string
    {}

    /**
     * Сборка объединения.
     * @param array union
     * @return string готовый sql-union
     */
    protected function buildUnion(array $union): string
    {}

    /**
     * Сборка нескольких объединений.
     * @param QueryBuilderInterface 
     * @return string готовые sql-union
     */
    protected function buildUnions(QueryBuilderInterface &$builder): string
    {}

    /**
     * Сборка сортировок.
     * @param array настроки сортировок
     * @return string готовый sql-order
     */
    protected function buildOrders(array $orders): string
    {}

    /**
     * Сборки лимита.
     * @param int|null лимит
     * @return string готовый sql-limit
     */
    protected function buildLimit(int $limit = null): string
    {}

    /**
     * Сборки сдвига.
     * @param int|null сдвиг
     * @return string готовый sql-offset
     */
    protected function buildOffset(int $offset = null): string
    {}
}
