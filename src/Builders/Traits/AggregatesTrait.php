<?php
namespace Evas\Db\Builders\Traits;

trait AggregatesTrait
{
    // Set aggregates

    public function aggregates(array $aggregates)
    {
        foreach ($aggregates as $function => $columns) {
            if (is_string($columns)) $columns = [$columns];
            $this->aggregate($function, $columns);
        }
        return $this;
    }

    public function aggregate(string $function, array $columns = ['*'])
    {
        $select = [];
        foreach ($columns as $column) {
            $as = strtolower($function) . "_{$column}";
            $select[$as] = strtoupper($function) . "({$this->wrapColumn($column)})";
        }
        $this->addSelect($select);
        return $this;
    }

    public function count(string $column)
    {
        return $this->aggregate('count', [$column]);
    }

    public function sum(string $column)
    {
        return $this->aggregate('sum', [$column]);
    }

    public function min(string $column)
    {
        return $this->aggregate('min', [$column]);
    }

    public function max(string $column)
    {
        return $this->aggregate('max', [$column]);
    }

    public function avg(string $column)
    {
        return $this->aggregate('avg', [$column]);
    }

    // Get aggregates

    public function getAggregates(array $aggregates)
    {
        return $this->aggregates($aggregates)->get();
    }

    public function getAggregate(string $function, array $columns = ['*'])
    {
        return $this->aggregate($function, $columns)->get();
    }

    public function getCount(string $column)
    {
        return $this->getAggregate('count', [$column]);
    }

    public function getSum(string $column)
    {
        return $this->getAggregate('sum', [$column]);
    }

    public function getMin(string $column)
    {
        return $this->getAggregate('min', [$column]);
    }

    public function getMax(string $column)
    {
        return $this->getAggregate('max', [$column]);
    }

    public function getAvg(string $column)
    {
        return $this->getAggregate('avg', [$column]);
    }
}
