<?php
/**
 * Расширенный класс соединения с базой данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\BaseDatabase;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Traits\DatabaseQueryTrait;
use Evas\Db\Traits\DatabaseSchemaCacheTrait;
use Evas\Db\Traits\DatabaseTablesTrait;

class Database extends BaseDatabase implements DatabaseInterface
{
    // расширенные запросы БД
    use DatabaseQueryTrait;
    // кэш схемы БД
    use DatabaseSchemaCacheTrait;
    // таблицы БД
    use DatabaseTablesTrait;
}
