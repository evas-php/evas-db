<?php
namespace Evas\Db\Builders\Options;

use Evas\Db\Builders\Options\AbstractOption;
use Evas\Db\Builders\Options\Traits\IsQueryableTrait;

class UnionOption extends AbstractOption
{
    use IsQueryableTrait;

    public $to = 'unions';

    public $query;
    public $all = false;

    /**
     * Конструктор.
     * @param array|null свойства
     */
    protected function __construct(array $props = null)
    {
        if ($props) foreach ($props as $name => $value) {
            $this->$name = $value;
        }
    }

    public static function union($query, bool $all = false)
    {
        if (!is_string($query) && !static::isQueryable($query)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be queryable or a string, %s given',
                __METHOD__, PhpHelp::getType($query)
            ));
        }
        // if ($query instanceof \Closure) {
        //     call_user_func($query, $query = $this->newQuery());
        // }
        // [$sql, $bindings] = $this->createSub($query);
        // $this->unions[] = compact('query', 'all');
        // return $this->addBindings('union', $query->getBindings());
        return new static($all ? 'All', 'Once', compact('query', 'all'));
    }

    public static function unionAll($query)
    {
        return static::union($query, true);
    }
}
