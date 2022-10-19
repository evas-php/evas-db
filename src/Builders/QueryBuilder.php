<?php
/**
 * Сборщик запросов SELECT/UPDATE/DELETE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Db\Interfaces\QueryBuilderInterface;

use Evas\Db\Builders\AbstractQueryBuilder;

use Evas\Db\Builders\Traits\SubQueryTrait;
use Evas\Db\Builders\Traits\WrapsTrait;
use Evas\Db\Builders\Traits\BindingsTrait;

use Evas\Db\Builders\Traits\AggregatesTrait;
use Evas\Db\Builders\Traits\SelectTrait;
use Evas\Db\Builders\Traits\FromTrait;

use Evas\Db\Builders\Traits\JoinsTrait;
use Evas\Db\Builders\Traits\WhereTrait;
use Evas\Db\Builders\Traits\GroupByTrait;
use Evas\Db\Builders\Traits\HavingTrait;
use Evas\Db\Builders\Traits\OrderByTrait;

class QueryBuilder extends AbstractQueryBuilder implements QueryBuilderInterface
{
    use SubQueryTrait;
    use WrapsTrait;
    use BindingsTrait;

    use AggregatesTrait;
    use SelectTrait;
    use FromTrait;

    use JoinsTrait;
    use WhereTrait;
    use GroupByTrait;
    use HavingTrait;
    use OrderByTrait;


    public $type = 'select';

    // public $from = [];
    // public $columns = [];
    // public $joins = [];
    // public $wheres = [];
    // public $groups = [];
    // public $havings = [];
    // public $orders = [];
    
    public $limit;
    public $offset;

    public $unions = [];
    
    // protected $bindings = [
    //     'from' => [],
    //     'update' => [],
    //     'columns' => [],
    //     'joins' => [],
    //     'wheres' => [],
    //     'havings' => [],
    //     'unions' => [],
    // ];


    /**
     * Установка лимита.
     * @param int|null лимит или null для сброса
     * @param int|null сдвиг или null для сброса
     * @return self
     */
    public function limit(int $limit = null, int $offset = null)
    {
        $this->limit = $limit < 1 ? null : $limit;
        return func_num_args() > 1 ? $this->offset($offset) : $this;
    }

    /**
     * Установка сдвига.
     * @param int|null сдвиг или null для сброса
     * @return self
     */
    public function offset(int $offset = null)
    {
        $this->offset = $offset < 1 ? null : $offset;
        return $this;
    }
}
