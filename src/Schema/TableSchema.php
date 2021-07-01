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

    /** @var bool все ли схемы были столбцов получены */
    protected $allColumnSchemasFetched = false;

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
        $this->name = $name;
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
        return $this->schemaFromCache()['primaryKey'];
    }

    /**
     * Получение схем столбцов из кэша, если включен.
     * @return array|null
     */
    public function columnSchemasFromCache(): ?array
    {
        return $this->schemaFromCache()['columns'];
    }

    /**
     * Получение внешних ключей из кэша, если включен.
     * @return array|null
     */
    public function foreignKeysFromCache(): ?array
    {
        return $this->schemaFromCache()['foreignKeys'];
    }

    /**
     * Получение имени таблицы.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Получение имени таблицы вместе с именем базы.
     * @return string
     */
    public function fullName(): string
    {
        return "`$this->db->dbname`.`$this->name`";
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
            if (false === $reload) {
                $this->primaryKey = $this->primaryKeyFromCache();
            }
            $row = $this->db->query("SHOW KEYS FROM `$this->name` WHERE Key_name = 'PRIMARY'")->assocArray();
            $this->primaryKey = $row['Column_name'];
        }
        if (empty($this->primaryKey)) {
            $table = $this->fullName();
            throw new TableSchemaException("Primary key does not exist in table `$table`");
            
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
        if (null === $this->columnSchemas || true === $reload) {
            if (false === $reload) {
                $this->columnSchemas = $this->columnSchemasFromCache();
            }
            if (null === $this->columnSchemas) {
                $rows = $this->db->query("SHOW COLUMNS FROM `{$this->db->dbname}`.`$this->name`")->assocArrayAll();
                $this->columnSchemas = [];
                if ($rows) foreach ($rows as &$row) {
                    $this->columnSchemas[$row['Field']] = new ColumnSchema($row);
                }
                $this->allColumnSchemasFetched = true;
            }
        }
        return $this->columnSchemas;
    }

    /**
     * Получение схемы колонки.
     * @param string имя колонки
     * @return ColumnSchema
     * @throws TableSchemaException
     */
    public function columnSchema(string $column): ColumnSchema
    {
        if (empty($this->columnSchemas[$column])) {
            $this->columnSchemas(true);
        }
        if (empty($this->columnSchemas[$column])) {
            $table = $this->fullName();
            throw new TableSchemaException("Not found column `$column` in table $table");
        }
        return $this->columnSchemas[$column];
    }

    /**
     * Получение столбцов таблицы.
     * @param bool|null сделать ли перезапрос схемы
     * @return array
     */
    public function columns(bool $reload = false): array
    {
        if (null === $this->columns || true === $reload) {
            if (true === $this->allColumnSchemasFetched) {
                $this->columns = array_keys($this->columnSchemas);
            } else {
                $rows = $this->db->query("SHOW COLUMNS FROM `{$this->db->dbname}`.`$this->name`")->assocArrayAll();
                $this->columns = [];
                if ($rows) foreach ($rows as &$row) {
                    $this->columns[] = $row['Field'];
                }
            }
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
        if (null === $this->foreignKeys || true === $reload) {
            if (false === $reload) {
                $this->foreignKeys = $this->foreignKeysFromCache();
            }
            if (null === $this->foreignKeys) {
                $sql = 'SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? 
                    AND CONSTRAINT_NAME <>"PRIMARY" 
                    AND REFERENCED_TABLE_NAME IS NOT NULL';
                $values = [$this->db->dbname, $this->name];
                $rows = $this->db->query($sql, $values)->assocArrayAll();
                $this->foreignKeys = [];
                if ($rows) foreach ($rows as &$row) {
                    $this->foreignKeys[$row['COLUMN_NAME']] = [
                        $row['REFERENCED_TABLE_SCHEMA'], 
                        $row['REFERENCED_TABLE_NAME'], 
                        $row['REFERENCED_COLUMN_NAME']
                    ];
                }
            }
        }
        return $this->foreignKeys;
    }
}
