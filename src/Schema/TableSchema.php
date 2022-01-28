<?php
/**
 * Класс схемы таблицы.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Schema;

use Evas\Db\Exceptions\TableSchemaException;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Schema\ColumnSchema;

class TableSchema
{
    /** @var DatabaseInterface */
    public $db;

    /** @var string имя таблицы */
    public $name;

    /** @var string первичный ключ */
    protected $primaryKey;

    /** @var array массив схем столбцов */
    protected $columnSchemas;

    /** @var array массив столбцов таблицы */
    protected $columns;

    /** @var array внешние ключи таблицы */
    protected $foreignKeys;

    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     * @param string имя таблицы
     */
    public function __construct(DatabaseInterface &$db, string $name)
    {
        $this->db = &$db;
        $this->name = $db->grammar()->unwrap($name);
        $tableSchema = $this->schemaFromCache();
        if (!empty($tableSchema)) {
            $this->primaryKey = $tableSchema['primaryKey'];
            $this->columns = array_keys($tableSchema['columns'] ?? []);
        }
    }

    /**
     * Получение схемы таблицы из кэша, если включен.
     * @return array|null
     */
    public function schemaFromCache(): ?array
    {
        if (method_exists($this->db, 'tableSchemaFromCache')) {
            return $this->db->tableSchemaFromCache($this->name);
        }
        return null;
    }

    /**
     * Получение первичного ключа из кэша, если включен.
     * @return string|null
     */
    public function primaryKeyFromCache(): ?string
    {
        return $this->schemaFromCache()['primaryKey'] ?? null;
    }

    /**
     * Получение схем столбцов из кэша, если включен.
     * @return array|null
     */
    public function columnSchemasFromCache(): ?array
    {
        return $this->schemaFromCache()['columns'] ?? null;
    }

    /**
     * Получение внешних ключей из кэша, если включен.
     * @return array|null
     */
    public function foreignKeysFromCache(): ?array
    {
        return $this->schemaFromCache()['foreignKeys'] ?? null;
    }

    /**
     * Получение первичного ключа таблицы.
     * @param bool|null сделать ли перезапрос схемы
     * @return string
     * @throws TableSchemaException
     */
    public function primaryKey(bool $reload = false): string
    {
        if (empty($this->primaryKey) || true === $reload) {
            if (true === $reload) {
                $this->primaryKey = $this->db->grammar()->getTablePrimaryKey($this->name);
            } else {
                $this->primaryKey = $this->primaryKeyFromCache();
                if (empty($this->primaryKey)) {
                    $this->db->updateSchemaCache();
                    $this->primaryKey = $this->primaryKeyFromCache();
                }
            }
        }
        if (empty($this->primaryKey)) {
            throw new TableSchemaException("Primary key does not exist in table: $this->name");
        }
        return $this->primaryKey;
    }

    /**
     * Получение схемы столбцов.
     * @param bool|null сделать ли перезапрос схемы
     * @return ColumnSchema[]
     */
    public function columnSchemas(bool $reload = false): array
    {
        if (empty($this->columnSchemas) || true === $reload) {
            if (true === $reload) {
                $rows = $this->db->grammar()->getTableColumns($this->name);
                $this->columnSchemas = [];
                if ($rows) foreach ($rows as &$row) {
                    $this->columnSchemas[$row['Field']] = new ColumnSchema($row);
                }
            } else {
                $this->columnSchemas = $this->columnSchemasFromCache();
                if (empty($this->columnSchemas)) {
                    $this->db->updateSchemaCache();
                    $this->columnSchemas = $this->columnSchemasFromCache();
                }
            }
        }
        return $this->columnSchemas;
    }

    /**
     * Получение схемы колонки.
     * @param string имя колонки
     * @param bool|null сделать ли перезапрос схемы
     * @return ColumnSchema
     * @throws TableSchemaException
     */
    public function columnSchema(string $column, bool $reload = false): ColumnSchema
    {
        if (empty($this->columnSchemas[$column]) || true === $reload) {
            $this->columnSchemas($reload);
        }
        if (empty($this->columnSchemas[$column])) {
            throw new TableSchemaException("Not found column `$column` in table: $this->name");
        }
        return new ColumnSchema($this->columnSchemas[$column]);
    }

    /**
     * Получение столбцов таблицы.
     * @param bool|null сделать ли перезапрос схемы
     * @return array
     */
    public function columns(bool $reload = false): array
    {
        if (empty($this->columns) || true === $reload) {
            $this->columns = array_keys($this->columnSchemas($reload));
        }
        return $this->columns;
    }

    /**
     * Получение внешних ключей таблицы.
     * @param bool|null сделать ли перезапрос
     * @return array
     */
    public function foreignKeys(bool $reload = false): array
    {
        if (empty($this->foreignKeys) || true === $reload) {
            if (true === $reload) {
                $this->foreignKeys = $this->db->grammar()->getForeignKeys($this->name) ?? [];
            } else {
                $this->foreignKeys = $this->foreignKeysFromCache();
                if (is_null($this->foreignKeys)) {
                    $this->db->updateSchemaCache();
                    $this->foreignKeys = $this->foreignKeysFromCache();
                }
            }
        }
        return $this->foreignKeys;
    }
}
