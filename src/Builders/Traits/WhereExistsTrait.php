<?php
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\BaseQueryBuilder;

trait WhereExistsTrait
{
    public function addWhereExistsQuery(BaseQueryBuilder $query, bool $isOr = false, bool $isNot = false)
    {
        [$sql, $values] = $query->getSqlAndBindings();
        return $this->pushWhere('Exists', compact('sql', 'values', 'isOr', 'isNot'));
    }

    public function whereExists(\Closure $callback, bool $isOr = false, bool $isNot = false)
    {
        call_user_func($callback, $query = $this->forSubQuery());
        return $this->addWhereExistsQuery($query, $isOr, $isNot);
    }

    public function orWhereExists(\Closure $callback)
    {
        return $this->whereExists($callback, true);
    }

    public function whereNotExists(\Closure $callback)
    {
        return $this->whereExists($callback, false, true);
    }

    public function orWhereNotExists(\Closure $callback)
    {
        return $this->whereExists($callback, true, true);
    }
}
