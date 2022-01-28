<?php
/**
 * Расширенный сборщик запросов SELECT/UPDATE/DELETE.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Db\Builders\BaseQueryBuilder;
use Evas\Db\Builders\JoinBuilder;
use Evas\Db\Builders\Traits\AggregatesTrait;
use Evas\Db\Builders\Traits\DateBasedWhereTrait;
use Evas\Db\Builders\Traits\HavingBetweenTrait;
use Evas\Db\Builders\Traits\HavingTrait;
use Evas\Db\Builders\Traits\QueryJoinsTrait;
use Evas\Db\Builders\Traits\SelectTrait;
use Evas\Db\Builders\Traits\UnionsTrait;
use Evas\Db\Builders\Traits\WhereBetweenTrait;
use Evas\Db\Builders\Traits\WhereExistsTrait;
use Evas\Db\Builders\Traits\WhereJsonTrait;
use Evas\Db\Builders\Traits\WhereRowValuesTrait;
use Evas\Db\Builders\Traits\WhereTrait;

class QueryBuilder extends BaseQueryBuilder
{
    /** Агрегаты */
    use AggregatesTrait;
    /** Where для даты и времени */
    use DateBasedWhereTrait;
    /** Having поддержка between */
    use HavingBetweenTrait;
    /** Having */
    use HavingTrait;
    /** Joins */
    use QueryJoinsTrait;
    /** Select columns */
    use SelectTrait;
    /** Unions */
    use UnionsTrait;
    /** Where between */
    use WhereBetweenTrait;
    /** Where Exists */
    use WhereExistsTrait;
    /** Where Json */
    use WhereJsonTrait;
    /** Where для сопоставления столбцов и значений */
    use WhereRowValuesTrait;
    /** Поддержка where */
    use WhereTrait;

    /**
     * @todo Locks
     */
}
