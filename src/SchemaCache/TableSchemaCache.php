<?php
/**
 * Кэш схемы таблицы.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\SchemaCache;

use Evas\Db\Exceptions\SchemaCacheException;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Schema\ColumnSchemaCache;

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
    }

    /**
     * Получение значения свойства таблицы из кэша схемы.
     * @param string имя свойства
     * @param bool|null сделать ли обновление схемы
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
     * @param bool|null сделать ли обновление схемы
     * @return string
     */
    public function primaryKey(bool $reload = false): string
    {
        return $this->valueFromCache('primaryKey', $reload);
    }

    /**
     * Получение столбцов таблицы.
     * @param bool|null сделать ли обновление схемы
     * @return array
     */
    public function columns(bool $reload = false): array
    {
        return $this->valueFromCache('columns', $reload);
    }

    /**
     * Получение внешних ключей таблицы.
     * @param bool|null сделать ли обновление схемы
     * @return array
     */
    public function foreignKeys(bool $reload = false): array
    {
        return $this->valueFromCache('foreignKeys', $reload);
    }
}
