<?php
/**
 * Класс кэша схемы.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Schema;

use Evas\Db\Exceptions\SchemaCacheException;
use Evas\Db\Interfaces\DatabaseInterface;

if (!defined('EVAS_DB_SCHEMA_CACHE_DIR')) {
    define('EVAS_DB_SCHEMA_CACHE_DIR', dirname(dirname(__DIR__)) . '/cache/schemas/');
    // define('EVAS_DB_SCHEMA_CACHE_DIR', 'config/db/schemas/');
}

class SchemaCache
{
    /** @var string путь к каталогу кэшей схем */
    public static $cacheDir = EVAS_DB_SCHEMA_CACHE_DIR;

    /** @var DatabaseInterface соединение с базой данных */
    public $db;

    /** @var string информация о соединении */
    public $dbInfo;

    /** @var string имя файла */
    public $filename;

    /** @var array схема */
    public $schema;

    /**
     * Конструктор.
     * @param DatabaseInterface соединение с базой данных
     * @throws SchemaCacheException
     */
    public function __construct(DatabaseInterface &$db)
    {
        if (empty($db->dbname)) {
            throw new SchemaCacheException('No database selected');
        }
        $this->db = &$db;
        $this->filename = "$db->host.$db->dbname.php";
        $this->dbInfo = "Host: {$this->db->host}; DbName: {$this->db->dbname};"
                . " Driver: {$this->db->driver}.";
    }

    /**
     * Подгрузка схемы базы данных из файла.
     */
    public function loadSchema()
    {
        $filepath = $this->getFilepath();
        if (is_file($filepath)) {
            $this->schema = include $filepath;
            evasDebug("Database schema for ($this->dbInfo) is received from the cache");
        }
        if (empty($this->schema) || !is_array($this->schema)) {
            $this->updateCache();
        }
    }

    /**
     * Обновление файла схемы базы данных.
     * @return array
     */
    public function updateCache()
    {
        $this->db->notUseSchemaCache();
        $tables = $this->db->tablesList();
        $this->schema = [];
        $lines = '';
        $count = 0;
        foreach ($tables as &$tableName) {
            $table = $this->db->table($tableName);
            $primaryKey = $table->primaryKey(true);
            $columns = $table->columnSchemas(true);
            $foreignKeys = $table->foreignKeys(true);

            $lines .= "    '$tableName' => [\n";
            $lines .= "        'primaryKey' => '$primaryKey',\n";
            $lines .= "        'columns' => [\n";
            foreach ($columns as $name => &$column) {
                $lines .= "            '$name' => [\n";
                foreach ($column as $key => &$value) {
                    if (is_string($value)) $value = "'" . addslashes($value) . "'";
                    if (empty($value)) $value = 'null';
                    $lines .= "                '$key' => $value,\n"; 
                }
                $lines .= "            ],\n";
            }
            $lines .= "        ],\n";
            $lines .= "        'foreignKeys' => [\n";
            foreach ($foreignKeys as $name => &$reference) {
                $lines .= "            '$name' => [\n";
                $lines .= "                '{$reference[0]}',\n";
                $lines .= "                '{$reference[1]}',\n";
                $lines .= "                '{$reference[2]}',\n";
                $lines .= "            ],\n";
            }
            $lines .= "        ],\n";
            $lines .= "    ],\n";
            $this->schema[$tableName] = compact('primaryKey', 'columns', 'foreignKeys');
            $count++;
        }
        $this->db->useSchemaCache();

        $data = "<?php\n/**\n * Database schema cache.\n"
        . " * $this->dbInfo\n *\n * Generated by " . self::class . "\n"
        . ' * on ' . date(\DATE_RFC7231) . "\n *\n"
        . " * Number of tables: $count\n"
        . " */\nreturn [\n$lines];\n";

        $savingPath = $this->getFilepath();
        $dir = dirname($savingPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        file_put_contents($savingPath, $data);
        evasDebug("Generated database schema cache for ($this->dbInfo)");
    }

    /**
     * Очистка кэша схемы базы данных.
     */
    public function clearCache()
    {
        $path = $this->getFilepath();
        if (!is_file($path)) {
            evasDebug("Database schema cache for ($this->dbInfo) has already been cleaned");
            return;
        }
        unlink($path);
        evasDebug("Clean database schema cache for ($this->dbInfo)");
    }

    /**
     * Получение имени файла схемы.
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Получение пути файла схемы.
     * @return string
     */
    public function getFilepath(): string
    {
        self::$cacheDir = str_replace('\\', '/', self::$cacheDir);
        if (strrpos(self::$cacheDir, '/') !== strlen(self::$cacheDir) - 1) {
            self::$cacheDir .= '/';
        }
        return self::$cacheDir . $this->filename;
    }

    /**
     * Получение схемы таблицы.
     * @param string имя таблицы
     * @return array|null
     */
    public function tableSchema(string $table): ?array
    {
        if (empty($this->schema)) $this->loadSchema();
        return $this->schema[$table] ?? null;
    }

    /**
     * Получение первичного ключа таблицы.
     * @param string имя таблицы
     * @return string|null
     */
    public function tablePrimaryKey(string $table): ?string
    {
        return $this->tableSchema($table)['primaryKey'];
    }

    /**
     * Получение схем столбцов таблицы.
     * @param string имя таблицы
     * @return array[]|null
     */
    public function tableColumnSchemas(string $table): ?array
    {
        return $this->tableSchema($table)['columns'];
    }

    /**
     * Получение списка столбцов таблицы.
     * @param string имя таблицы
     * @return array|null
     */
    public function tableColumns(string $table): ?array
    {
        $columns = $this->tableColumnSchemas($tables);
        return empty($columns) ? null : array_keys($columns);
    }
}
