<?php
/**
 * Базовая граматика сборки запросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars;

use Evas\Db\Builders\InsertBuilder;
use Evas\Db\Builders\BaseQueryBuilder;
use Evas\Db\Interfaces\DatabaseInterface;

class Grammar
{
    /** @var DatabaseInterface соединение с базой данных */
    protected $db;

    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     */
    public function __construct(DatabaseInterface &$db)
    {
        $this->db = &$db;
    }

    // ----------
    // Wraps
    // ----------

    public function unwrap(string $value): string
    {
        return trim($value, '`');
    }

    public function unwrapTable(string $value): string
    {
        return $this->unwrap($value);
    }

    public function wrap(string $value): string
    {
        $value = $this->unwrap($value);
        return '*' === $value ? $value : "`$value`";
    }

    public function wrapTable(string $value): string
    {
        return $this->wrap($value);
    }

    public function wrapColumn(string $column): string
    {
        $parts = explode('.', $column);
        foreach ($parts as &$part) {
            $part = $this->wrap($part);
        }
        return implode('.', $parts);
    }

    public function wrapColumns(array $columns): string
    {
        foreach ($columns as &$column) {
            if (!strstr($column, 'AS')) {
                $column = $this->wrapColumn($column);
            }
        }
        return implode(', ', $columns);
    }

    public function wrapStringColumns(string $value): string
    {
        $columns = explode(',', str_replace(', ', ',', $value));
        return $this->wrapColumns($columns);
    }


    // ----------
    // Build insert
    // ----------

    public function buildInsert(InsertBuilder &$builder): string
    {
        $keys = "({$this->wrapColumns($builder->keys)})";
        // $keys = '(' . implode(', ', $builder->keys) . ')';
        $quote = '('. implode(', ', array_fill(0, count($builder->keys), '?')) .')';
        if ($builder->rowCount() > 1) {
            $quote = implode(', ', array_fill(0, $builder->rowCount(), $quote));
        }
        $sql = "INSERT INTO {$this->wrapTable($builder->tbl)} $keys VALUES $quote";
        // $sql = "INSERT INTO public.\"{$builder->tbl}\" $keys VALUES $quote";
        return $sql;
    }

    // ----------
    // Build query from BaseQueryBuilder
    // ----------

    // const BUILD_QUERY_FUNCS = [
    //     'select' => 'buildSelect',
    //     'update' => 'buildUpdate',
    //     'delete' => 'buildDelete',
    // ];

    // public function buildQueryFromBuilder(BaseQueryBuilder &$builder): string
    // {
    //     if (empty($builder->from)) {
    //         throw new \InvalidArgumentException('Table name not exists');
    //     }
    //     $func = static::BUILD_QUERY_FUNCS[$builder->type] ?? static::BUILD_QUERY_FUNCS['select'];
    //     return $this->{$func}($builder);
    // }


    public function buildQuery(BaseQueryBuilder &$builder): string
    {
        if (empty($builder->from)) {
            throw new \InvalidArgumentException('Table name not exists in BaseQueryBuilder');
        }
        if ('update' === $builder->type) {
            $sql = "UPDATE $builder->from SET $builder->updateSql";
        } else if ('delete' === $builder->type) {
            $sql = "DELETE FROM $builder->from";
        } else {
            $builder->type = 'select';
            $cols = $builder->columns;
            if (empty($cols)) $cols = ['*'];
            $cols = implode(', ', $cols);
            $sql = "SELECT $cols FROM $builder->from";
        }
        if ('select' == $builder->type && count($builder->joins)) {
            $sql .= $this->buildJoins($builder->joins);
        }
        if (count($builder->wheres)) {
            $where = $this->buildWheres($builder->wheres);
            if ($where) $sql .= " WHERE $where";
        }

        if ('select' == $builder->type) {
            if (count($builder->groups)) {
                $sql .= $this->buildGroups($builder->groups);
            }
            if (count($builder->havings)) {
                $having = $this->buildWheres($builder->havings);
                if ($having) $sql .= " HAVING $having";
            }
        }
        if (count($builder->unions)) {
            $sql .= $this->buildUnions($builder);
        }
        if (count($builder->orders)) {
            $sql .= $this->buildOrders($builder->orders);
        }
        if (!empty($builder->limit)) {
            $sql .= $this->buildLimit($builder->limit);
        }
        if (!empty($builder->offset)) {
            $sql .= $this->buildOffset($builder->offset);
        }
        return $sql;
    }

    // // ----------
    // // Build update
    // // ----------

    // public function buildUpdate(BaseQueryBuilder &$builder): string
    // {
    //     // 
    // }

    // // ----------
    // // Build delete
    // // ----------

    // public function buildDelete(BaseQueryBuilder &$builder): string
    // {
    //     // 
    // }



    // // ----------
    // // Build select
    // // ----------

    // public function buildSelect(BaseQueryBuilder &$builder): string
    // {
    //     // 
    // }



    // ----------
    // Build Wheres & Havings
    // ----------

    public function buildWheres(array &$wheres): string
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

    protected function getWhereSeparator(array $where): string
    {
        return ($where['isOr'] ?? null) ? ' OR ' : ' AND ';
    }

    protected function getNot(array $where): string
    {
        return ($where['isNot'] ?? null) ? 'NOT ' : '';
    }


    protected function buildWhereSingle(array $where)
    {
        return "{$this->wrapColumn($where['column'])} {$where['operator']} ?";
    }

    protected function buildWhereSingleColumn(array $where)
    {
        return "{$this->wrapColumn($where['first'])} {$where['operator']} {$this->wrapColumn($where['second'])}";
    }

    protected function buildWhereNested(array $where)
    {
        return "({$where['sql']})";
    }

    protected function buildWhereSub(array $where)
    {
        return "{$this->wrapColumn($where['column'])} {$where['operator']} ({$where['sql']})";
    }

    protected function buildWhereRaw(array $where)
    {
        return $where['sql'];
    }

    protected function buildWhereNull(array $where)
    {
        return "{$this->wrapColumn($where['column'])} IS {$this->getNot($where)}NULL";
    }

    protected function buildWhereIn(array $where)
    {
        $quotes = implode(', ', array_fill(0, count($where['values']), '?'));
        return "{$this->wrapColumn($where['column'])} {$this->getNot($where)}IN({$quotes})";
    }

    protected function buildWhereExists(array $where)
    {
        return "{$this->getNot($where)}EXISTS ({$where['sql']})";
    }

    protected function buildWhereBetween(array $where)
    {
        return "{$this->wrapColumn($where['column'])} {$this->getNot($where)}BETWEEN ? AND ?";
    }

    protected function buildWhereBetweenColumns(array $where)
    {
        $not = $this->getNot($where);
        $min = $this->wrapColumn($where['columns'][0]);
        $max = $this->wrapColumn($where['columns'][1]);
        return "{$this->wrapColumn($where['column'])} {$not}BETWEEN {$min} AND {$max}";
    }

    protected function buildWhereDateBased(array $where)
    {
        $not = $this->getNot($where);
        return "{$where['date_operator']}({$this->wrapColumn($where['column'])}) {$where['operator']} ?";
    }

    protected function buildWhereBetweenDateBased(array $where)
    {
        $not = $this->getNot($where);
        return "{$where['date_operator']}({$this->wrapColumn($where['column'])}) {$not}BETWEEN ? AND ?";
    }


    protected function buildWhereRowValues(array $where)
    {
        $sql = '';
        foreach ($where['columns'] as $i => $column) {
            if ($i > 0) $sql .= $this->getWhereSeparator($where);
            $sql .= "{$this->wrapColumn($column)} {$where['operator']} ?";
        }
        return "($sql)";
    }


    // ----------
    // Build JOINS
    // ----------

    public function buildJoins(array $joins): string
    {
        $sql = ' ';
        foreach ($joins as $i => &$join) {
            if ($i > 0) $sql += ' ';
            $sql .= $join->getSql();
        }
        return $sql;
    }

    public function buildJoin($join): string
    {
        $sql = "$join->type JOIN";
        if (!empty($join->as)) {
            $sql .= " ({$join->from}) AS {$this->wrapTable($join->as)}";
        } else {
            $sql .= " {$this->wrapTable($join->from)}";
        }
        if (count($join->on)) {
            $sql .= ' ON ' . $this->buildWheres($join->on);
        }
        if (!empty($join->using)) {
            $sql .= " USING ({$this->wrapColumn($join->using)})";
        }
        return $sql;
    }



    // ----------
    // GROUPS
    // ----------

    public function buildGroups(array $groups): string
    {
        return ' GROUP BY ' . implode(', ', $groups);
    }


    // ----------
    // UNIONS
    // ----------

    public function buildUnions(BaseQueryBuilder &$builder): string
    {
        $sql = '';
        foreach ($builder->unions as &$union) {
            if (!count($union['query']->columns)) {
                // устанавливаем столбцы для JOIN из запроса, если не установлены
                $union['query']->columns = $builder->columns;
            }
            $sql .= $this->buildUnion($union);
        }
        return $sql;
    }

    public function buildUnion(array $union): string
    {
        $all = ($union['all'] || false) ? ' ALL' : '';
        $sql = $union['query']->getSql();
        return " UNION {$all} ({$sql})";
    }

    // ----------
    // ORDERING
    // ----------

    public function buildOrders(array $orders): string
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

    // ----------
    // LIMIT & OFFSET
    // ----------

    public function buildLimit(int $limit = null): string
    {
        return $limit > 0 ? " LIMIT {$limit}" : '';
    }

    public function buildOffset(int $offset = null): string
    {
        return $offset > 0 ? " OFFSET {$offset}" : '';
    }


    // ----------
    // Schema Cache
    // ----------

    public function getTablesList(): array
    {
        return [];
    }

    public function getTablePrimaryKey(string $table)
    {
        return 'id';
    }

    public function getTableColumns(string $table)
    {
        return [];
    }

    public function getForeignKeys(string $table)
    {
        return [];
    }
}
