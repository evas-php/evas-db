<?php
namespace Evas\Db\Builders\Traits;

use Evas\Base\Help\PhpHelp;

trait UnionsTrait
{
    public function union($query, bool $all = false)
    {
        if (!$this->isQueryable($query)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be queryable, %s given',
                __METHOD__, PhpHelp::getType($query)
            ));
        }
        if ($query instanceof \Closure) {
            call_user_func($query, $query = $this->newQuery());
        }
        // [$sql, $bindings] = $this->createSub($query);
        $this->unions[] = compact('query', 'all');
        return $this->addBindings('union', $query->getBindings());
    }

    public function unionAll($query)
    {
        return $this->union($query, true);
    }
}
