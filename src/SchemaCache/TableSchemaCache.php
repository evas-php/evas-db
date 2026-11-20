<?php
/**
 * Кэш схемы таблицы.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\SchemaCache;

use Evas\Db\Exceptions\SchemaCacheException;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\SchemaCache\ColumnSchemaCache;

class TableSchemaCache
{
    /** @var DatabaseInterface */
    public $db;
    /** @var string имя таблицы */
    public $name;
    /** @var string первичный ключ */
    protected $primaryKey;
    /** @var array массив схем столбцов */
    // protected $columnSchemas;
    /** @var array массив столбцов таблицы */
    protected $columnKeys;
    /** @var array массив внешних ключей таблицы */
    protected $foreignKeys;
    /** @var array столбцы таблицы */
    protected $columns;
    /** @var ColumnSchemaCache[] кэшированный массив схем столбцов */
    protected $columnSchemas;

    /**
     * Конструктор.
     * @param DatabaseInterface $db соединение с базой данных
     * @param string $name имя таблицы
     */
    public function __construct(DatabaseInterface &$db, string $name)
    {
        $this->db = &$db;
        $this->name = $db->grammar()->unwrap($name);
    }

    /**
     * Получение значения свойства таблицы из кэша схемы.
     * @param string $name имя свойства
     * @param bool|null $reload сделать ли обновление схемы
     * @return mixed
     */
    protected function valueFromCache(string $name, bool $reload = false)
    {
        if (is_null($this->$name) || true === $reload) {
            $tableCache = $this->db->schemaCache()->table($this->name, $reload);
            foreach ($tableCache as $_name => $value) {
                $this->$_name = $value;
            }
        }
        return $this->$name;
    }

    /**
     * Получение первичного ключа таблицы.
     * @param bool|null $reload сделать ли обновление схемы
     * @return string
     */
    public function primaryKey(bool $reload = false): string
    {
        return $this->valueFromCache('primaryKey', $reload);
    }

    /**
     * Получение столбцов таблицы.
     * @param bool|null $reload сделать ли обновление схемы
     * @return array
     */
    public function columns(bool $reload = false): array
    {
        return $this->valueFromCache('columnKeys', $reload);
    }

    /**
     * Получение схем столбцов таблицы.
     * @param bool|null $reload сделать ли обновление схемы
     * @return array
     */
    public function columnSchemas(bool $reload = false): array
    {
        if (empty($this->columnSchemas) || $reload) {
            $this->columnSchemas = array_map(
                fn ($column) => new ColumnSchemaCache($column), 
                $this->valueFromCache('columns', $reload)
            );
        }
        return $this->columnSchemas;
    }

    /**
     * Получение схемы столбца таблицы.
     * @param string $column имя столбца
     * @param bool|null $reload сделать ли обновление схемы
     * @return array
     */
    public function columnSchema(string $column, bool $reload = false): ?ColumnSchemaCache
    {
        $columnSchemas = $this->columnSchemas($reload);
        return $columnSchemas[$column] ?? null;
    }

    /**
     * Проверка наличия столбца в таблице.
     * @param string $column имя столбца
     * @param bool|null $reload сделать ли обновление схемы
     * @return bool
     */
    public function hasColumn(string $column, bool $reload = false): bool
    {
        return isset($this->columns($reload)[$column]);
    }

    /**
     * Получение внешних ключей таблицы.
     * @param bool|null $reload сделать ли обновление схемы
     * @return array
     */
    public function foreignKeys(bool $reload = false): array
    {
        return $this->valueFromCache('foreignKeys', $reload);
    }
}
