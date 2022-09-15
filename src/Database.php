<?php
namespace Evas\Db;

use Evas\Db\BaseDatabase;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Traits\DatabaseQueryTrait;

class Database extends BaseDatabase implements DatabaseInterface
{
    // расширенные запросы БД
    use DatabaseQueryTrait;
    // таблицы БД
}
