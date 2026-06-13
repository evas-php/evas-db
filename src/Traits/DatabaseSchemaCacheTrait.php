<?php
/**
 * Трейт для кэша схемы базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\SchemaCache\SchemaCache;

trait DatabaseSchemaCacheTrait
{
    /** @var SchemaCache кэш схемы БД */
    protected $schemaCache;

    /**
     * Получение кэша схемы БД.
     * @return SchemaCache
     */
    public function schemaCache(): SchemaCache
    {
        if (null === $this->schemaCache) {
            $this->schemaCache = new SchemaCache($this);
        }
        return $this->schemaCache;
    }
}
