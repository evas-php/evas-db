<?php
/**
 * Класс соединения с базой данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Base\BaseDatabase;
use Evas\Db\Traits\DatabaseBuildersTrait;
use Evas\Db\Traits\DatabaseIdentityMapTrait;
use Evas\Db\Traits\DatabaseTableTrait;
use Evas\Db\Traits\DatabaseSchemaCacheTrait;
use Evas\Db\Interfaces\DatabaseQueryBuildersInterface;

class Database extends BaseDatabase implements DatabaseQueryBuildersInterface
{
    /**
     * Подключаем поддержку: 
     * сборщиков запросов, классов таблиц, маппинга идентичности сущностей,
     * кэша схемы базы данных.
     */
    use DatabaseBuildersTrait, DatabaseTableTrait, DatabaseIdentityMapTrait;
    use DatabaseSchemaCacheTrait;
}
