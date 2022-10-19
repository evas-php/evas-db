<?php
/**
 * Интерфейс сборщика заросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryResultInterface;
use Evas\Db\Interfaces\BaseQueryBuilderInterface;

interface QueryBuilderInterface extends BaseQueryBuilderInterface
{
    
    public function fromRaw(string $sql, array $bindings = []);

    public function fromSub($query, string $as);
}
