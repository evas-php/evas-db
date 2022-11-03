<?php
/**
 * Сборщик запросов SELECT/UPDATE/DELETE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;


use Evas\Db\Builders\BaseQueryBuilder;

use Evas\Db\Builders\Traits\AggregatesTrait;
use Evas\Db\Builders\Traits\SelectTrait;

use Evas\Db\Builders\Traits\JoinsTrait;
use Evas\Db\Builders\Traits\JoinsWhereTrait;
use Evas\Db\Builders\Traits\JoinsUsingTrait;

use Evas\Db\Builders\Traits\WhereTrait;
use Evas\Db\Builders\Traits\WhereBetweenColumnsTrait;
use Evas\Db\Builders\Traits\WhereBetweenTrait;
use Evas\Db\Builders\Traits\WhereDateBasedBetweenTrait;
use Evas\Db\Builders\Traits\WhereDateBasedTrait;
use Evas\Db\Builders\Traits\WhereExistsTrait;
// use Evas\Db\Builders\Traits\WhereJsonTrait;
use Evas\Db\Builders\Traits\WhereRowTrait;

use Evas\Db\Builders\Traits\GroupByTrait;
use Evas\Db\Builders\Traits\HavingTrait;
use Evas\Db\Builders\Traits\HavingBetweenTrait;
use Evas\Db\Builders\Traits\HavingBetweenColumnsTrait;
use Evas\Db\Builders\Traits\UnionsTrait;
use Evas\Db\Builders\Traits\OrderByTrait;

use Evas\Db\Interfaces\QueryBuilderInterface;

class QueryBuilder extends BaseQueryBuilder implements QueryBuilderInterface
{
    use AggregatesTrait;
    use SelectTrait;

    use JoinsTrait;
    use JoinsWhereTrait;
    use JoinsUsingTrait;
    
    use WhereTrait;
    use WhereBetweenColumnsTrait;
    use WhereBetweenTrait;
    use WhereDateBasedBetweenTrait;
    use WhereDateBasedTrait;
    use WhereExistsTrait;
    // use WhereJsonTrait;
    use WhereRowTrait;

    use GroupByTrait;
    use HavingTrait;
    use HavingBetweenTrait;
    use HavingBetweenColumnsTrait;
    use UnionsTrait;
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
