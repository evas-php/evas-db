<?php
/**
 * Трейт грамматики запросов СУБД.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars\Traits;

use Evas\Db\Interfaces\InsertBuilderInterface;
// use Evas\Db\Interfaces\JoinBuilderInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;
use Evas\Db\Grammars\Traits\GrammarQueryWhereTrait;

trait GrammarQueryTrait
{
    // Сборка where или having условий
    use GrammarQueryWhereTrait;

    /**
     * Сборка ?.
     * @param int количество экранируемых значений
     * @return string sql
     */
    protected function quotes(int $count)
    {
        return '('. implode(', ', array_fill(0, $count, '?')) .')';
    }

    /**
     * Сборка insert запроса.
     * @param InsertBuilderInterface
     * @return string готовый insert запрос
     */
    public function buildInsert(InsertBuilderInterface &$builder): string
    {
        $columns = "({$this->wrapColumns($builder->columns)})";
        $quotes = $this->quotes(count($builder->columns));
        if ($builder->getRowCount() > 1) {
            $quotes = implode(', ', array_fill(0, $builder->getRowCount(), $quotes));
        }
        $sql = "INSERT INTO {$this->wrap($builder->table)} $columns VALUES $quotes";
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
        // var_dump($builder);
        // return '';
        if (empty($builder->from)) {
            throw new \InvalidArgumentException('Not has table name in QueryBuilder');
        }
        $from = $this->buildFrom($builder->from);
        if ('update' === $builder->type) {
            $sql = "UPDATE {$from} SET {$builder->updateSql}";
        } else if ('delete' === $builder->type) {
            $sql = "DELETE FROM {$from}";
        } else {
            $builder->type = 'select';
            $cols = $this->buildColumns($builder);
            $sql = "SELECT {$cols} FROM {$from}";
        }
        if (count($builder->joins)) $sql .= $this->buildJoins($builder->joins);
        if (count($builder->wheres)) {
            $where = $this->buildWheres($builder->wheres);
            if ($where) $sql .= " WHERE {$where}";
        }
        if ('select' == $builder->type) {
            $hasGroupBy = count($builder->groups) > 0;
            if ($hasGroupBy) {
                $sql .= $this->buildGroups($builder->groups);
            }
            if (count($builder->havings)) {
                if (!$hasGroupBy) {
                    throw new \RuntimeException('QueryBuilder has HAVING without GROUP BY');
                }
                $having = $this->buildWheres($builder->havings);
                if ($having) $sql .= " HAVING {$having}";
            }
        }
        if (count($builder->unions)) $sql .= $this->buildUnions($builder);
        if (count($builder->orders)) $sql .= $this->buildOrders($builder->orders);
        $sql .= $this->buildLimit($builder->limit);
        $sql .= $this->buildOffset($builder->offset);
        return $sql;
    }


    // Частичная сборка select-запроса

    /**
     * Сборка columns.
     */
    protected function buildColumns(QueryBuilderInterface &$builder): string
    {
        $cols = $builder->aggregates ?? null;
        if (empty($cols)) $cols = $builder->columns;
        if (empty($cols)) $cols = ['*'];
        // $cols = $this->wrapColumns($cols);
        $cols = implode(', ', $cols);
        return $cols;

    }

    /**
     * Сборка FROM.
     */
    protected function buildFrom(array $from): string
    {
        // $from = $this->wrapColumns($from);
        $from = implode(', ', $from);
        return $from;
    }

    /**
     * Сборка нескольких join.
     * @param 
     * @return string готовые sql-join
     */
    protected function buildJoins(array $joins): string
    {
        $sql = ' ';
        foreach ($joins as $i => &$join) {
            if ($i > 0) $sql .= ' ';
            $sql .= $join->getSql();
            // $sql .= $this->buildJoin($join);
        }
        return $sql;
    }

    /**
     * Сборка join.
     * @param 
     * @return string готовый sql-join
     */
    public function buildJoin($join): string
    {
        $sql = "{$join->type} JOIN {$this->buildFrom($join->from)}";
        if (count($join->on)) {
            $sql .= ' ON ' . $this->buildWheres($join->on);
        } else if (!empty($join->using)) {
            $sql .= " USING ({$this->wrap($join->using)})";
        }
        return $sql;
    }

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
     * Сборка группировки.
     * @param array группировки
     * @return string готовый sql-group
     */
    protected function buildGroups(array $groups): string
    {
        return ' GROUP BY ' . implode(', ', $groups);
    }

    /**
     * Сборка объединений.
     * @param QueryBuilderInterface 
     * @return string готовые sql-union
     */
    protected function buildUnions(QueryBuilderInterface &$builder): string
    {
        $sql = '';
        foreach ($builder->unions as &$union) {
            $all = ($union['all'] || false) ? ' ALL' : '';
            $sql .= " UNION {$all}{$union['sql']}";
        }
        return $sql;
    }

    /**
     * Сборка сортировок.
     * @param array настроки сортировок
     * @return string готовый sql-order
     */
    protected function buildOrders(array $orders): string
    {
        $sql = ' ORDER BY ';
        foreach ($orders as $i => $order) {
            if ($i > 0) $sql .= ', ';
            @[$_sql, $isDesc] = $order;
            $sql .= $_sql;
            if ($isDesc) $sql .= ' DESC';
        }
        return $sql;
    }

    /**
     * Сборки лимита.
     * @param int|null лимит
     * @return string готовый sql-limit
     */
    protected function buildLimit(int $limit = null): string
    {
        return $limit > 0 ? " LIMIT {$limit}" : '';
    }

    /**
     * Сборки сдвига.
     * @param int|null сдвиг
     * @return string готовый sql-offset
     */
    protected function buildOffset(int $offset = null): string
    {
        return $offset > 0 ? " OFFSET {$offset}" : '';
    }
}
