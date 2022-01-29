<?php
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\JoinBuilder;

trait QueryJoinsTrait
{
    /** @var array joins */
    public $joins = [];

    protected function makeJoin(string $type, array $table): JoinBuilder
    {
        @[$query, $as] = $table;
        if ($as) {
            [$sql, $values] = $this->createSub($query);
            $this->addBindings('join', $values);
            $join = new JoinBuilder($this, $type, $sql);
            $join->as($as);
        } else {
            $join = new JoinBuilder($this, $type, $query);
        }
        return $join;
    }

    protected function endJoin(JoinBuilder $join, array $condition, bool $where = false)
    {
        @[$first, $operator, $second] = $condition;
        if ($first instanceof \Closure) {
            $first($join);
        } else {
            if (count($condition) === 1 && is_string($first)) $join->using($this->wrapColumn($first));
            else if ($where) $this->whereColumn(...$condition);
            else $join->on(...$condition);
        }
        return $this->addJoin($join);
    }

    protected function addJoin(JoinBuilder $join)
    {
        $bindings = $join->getBindings();
        if (count($bindings)) $this->addBindings('join', $bindings);
        $this->joins[] = $join;
        return $this;
    }

    protected function realSetJoin(string $type, array $table, array $condition, bool $where = false)
    {
        return $this->endJoin(
            $this->makeJoin($type, $table),
            $condition, $where
        );
    }

    protected function setJoin(string $type, string $table, $first, string $operator = null, string $second = null)
    {
        $condition = func_get_args();
        return $this->realSetJoin(array_shift($condition), [array_shift($condition)], $condition);
    }


    protected function setJoinWhere(string $type, string $table, $first, string $operator = null, string $second = null)
    {
        $condition = func_get_args();
        return $this->realSetJoin(array_shift($condition), [array_shift($condition)], $condition, true);
    }

    protected function setJoinSub(string $type, $query, string $as, $first, string $operator = null, string $second = null)
    {
        $condition = func_get_args();
        return $this->realSetJoin(array_shift($condition), [
            array_shift($condition), array_shift($condition)
        ], $condition);
    }

    public function setJoinSubWhere(string $type, $query, string $as, $first, string $operator = null, string $second = null)
    {
        $condition = func_get_args();
        return $this->realSetJoin(array_shift($condition), [
            array_shift($condition), array_shift($condition)
        ], $condition, true);
    }


    // INNER JOIN

    public function join(string $table, $first, string $operator = null, string $second = null)
    {
        return $this->setJoin('INNER', ...func_get_args());
    }

    public function joinWhere(string $table, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinWhere('INNER', ...func_get_args());
    }

    public function joinSub($query, string $as, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinSub('INNER', ...func_get_args());
    }

    public function joinSubWhere($query, string $as, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinSubWhere('INNER', ...func_get_args());
    }

    public function joinUsing(string $table, string $column, string $type = 'INNER')
    {
        return $this->setJoin($type, [$table], [$column]);
    }

    public function joinSubUsing($query, string $as, string $column, string $type = 'INNER')
    {
        return $this->setJoin($type, [$query, $as], [$column]);
    }


    // LEFT JOIN

    public function leftJoin(string $table, $first, string $operator = null, string $second = null)
    {
        return $this->setJoin('LEFT', ...func_get_args());
    }

    public function leftJoinSub($query, string $as, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinSub('LEFT', ...func_get_args());
    }

    public function leftJoinUsing(string $table, string $column)
    {
        return $this->joinUsing($table, $column, 'LEFT');
    }

    public function leftJoinSubUsing($query, string $as, string $column)
    {
        return $this->joinSubUsing($query, $as, $column, 'LEFT');
    }


    // LEFT OUTER JOIN

    public function leftOuterJoin(string $table, $first, string $operator = null, string $second = null)
    {
        return $this->setJoin('LEFT OUTER', ...func_get_args());
    }

    public function leftOuterJoinSub($query, string $as, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinSub('LEFT OUTER', ...func_get_args());
    }

    public function leftOuterJoinUsing(string $table, string $column)
    {
        return $this->joinUsing($table, $column, 'LEFT OUTER');
    }

    public function leftOuterJoinSubUsing($query, string $as, string $column)
    {
        return $this->joinSubUsing($query, $as, $column, 'LEFT OUTER');
    }

    // RIGHT JOIN

    public function rightJoin(string $table, $first, string $operator = null, string $second = null)
    {
        return $this->setJoin('RIGHT', ...func_get_args());
    }

    public function rightJoinSub($query, string $as, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinSub('RIGHT', ...func_get_args());
    }

    public function rightJoinUsing(string $table, string $column)
    {
        return $this->joinUsing($table, $column, 'RIGHT');
    }

    public function rightJoinSubUsing($query, string $as, string $column)
    {
        return $this->joinSubUsing($query, $as, $column, 'RIGHT');
    }

    // RIGHT OUTER JOIN

    public function rightOuterJoin(string $table, $first, string $operator = null, string $second = null)
    {
        return $this->setJoin('RIGHT OUTER', ...func_get_args());
    }

    public function rightOuterJoinSub($query, string $as, $first, string $operator = null, string $second = null)
    {
        return $this->setJoinSub('RIGHT OUTER', ...func_get_args());
    }

    public function rightOuterJoinUsing(string $table, string $column)
    {
        return $this->joinUsing($table, $column, 'RIGHT OUTER');
    }

    public function rightOuterJoinSubUsing($query, string $as, string $column)
    {
        return $this->joinSubUsing($query, $as, $column, 'RIGHT OUTER');
    }
}
