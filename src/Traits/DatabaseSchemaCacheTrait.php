<?php
/**
 * Трейт расширения базы данных подддержкой кэширования схемы.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Schema\SchemaCache;

trait DatabaseSchemaCacheTrait
{
    /** @var bool используется ли кэш схемы */
    protected $useSchemaCache = true;

    /** @var SchemaCache объект кэша схемы */
    protected $schemaCache;

    /**
     * Использовать кэш схемы.
     * @return object
     */
    public function useSchemaCache(): object
    {
        $this->useSchemaCache = true;
        return $this;
    }

    /**
     * Не использовать кэш схемы.
     * @return object
     */
    public function notUseSchemaCache(): object
    {
        $this->useSchemaCache = false;
        return $this;
    }

    /**
     * Используется ли кэш схемы.
     * @return bool
     */
    public function isSchemaCacheUsed(): bool
    {
        return $this->useSchemaCache;
    }

    /**
     * Получение объекта кэша схемы, если кэш используется.
     * @return SchemaCache|null
     */
    public function schemaCache(): ?SchemaCache
    {
        if ($this->isSchemaCacheUsed()) {
            if (empty($this->schemaCache)) {
                $this->schemaCache = new SchemaCache($this);
            }
            return $this->schemaCache;
        }
        return null;
    }

    /**
     * Обновление кэша схемы базы данных, если кэш используется.
     * @return bool используется ли кэш
     */
    public function updateSchemaCache(): bool
    {
        if ($this->isSchemaCacheUsed()) {
            $this->schemaCache()->updateCache();
            return true;
        }
        return false;
    }

    /**
     * Сброс кэша схемы базы данных, если кэш используется.
     * @return bool используется ли кэш
     */
    public function clearSchemaCache(): bool
    {
        if ($this->isSchemaCacheUsed()) {
            $this->schemaCache()->clearCache();
            return true;
        }
        return false;
    }

    /**
     * Получение схемы таблицы из кэша, если кэш используется.
     * @param string имя таблицы
     * @return array|null
     */
    public function tableSchemaFromCache(string $table): ?array
    {
        $schemaCache = $this->schemaCache();
        if (!empty($schemaCache)) {
            return $schemaCache->tableSchema($table);
        }
        return null;
    }
}
