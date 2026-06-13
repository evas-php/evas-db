<?php
/**
 * Кэш схемы базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\SchemaCache;

use Evas\Base\App;
use Evas\Base\Help\PhpConfigRender;
use Evas\Db\Exceptions\SchemaCacheException;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\SchemaCacheInterface;

if (!defined('EVAS_DB_SCHEMA_CACHE_DIR')) {
    define('EVAS_DB_SCHEMA_CACHE_DIR', 'cache/schemas/');
}

class SchemaCache implements SchemaCacheInterface
{
    /** @static float актуальная версия кэша схемы */
    const VERSION = 1;

    /** @var string путь к каталогу кэшей схем */
    public static $cacheDir = EVAS_DB_SCHEMA_CACHE_DIR;

    /** @var DatabaseInterface соединение с базой данных */
    public $db;
    /** @var string путь файла */
    protected $filepath;
    /** @var array схема */
    protected $schema = [];

    public static $debugMessages = [
        'received' => 'Database schema cache "%s" is received from the cache',
        'updated' => 'Database schema cache "%s" updated',
        'already_clear' => 'Database schema cache "%s" has already been cleaned',
        'clear' => 'Database schema cache "%s" cleared',
    ];

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
        $this->filepath = $this->filepath();
    }

    /**
     * Получение имени файла схемы.
     * @return string
     */
    public function filename(): string
    {
        return "{$this->db->driver}.{$this->db->host}.{$this->db->dbname}.php";
    }

    /**
     * Получение пути файла схемы.
     * @return string
     */
    public function filepath(): string
    {
        if (null === $this->filepath) {
            $this->filepath = App::resolveByApp(self::$cacheDir) . $this->filename();
        }
        return $this->filepath;
    }

    /**
     * Получение списка таблиц.
     * @param bool|null сделать ли принудительное обновление схемы
     * @return array
     */
    public function tablesList(bool $reload = false): array
    {
        if (true === $reload) $this->update();
        $this->loadIfNotLoaded();
        return array_keys($this->schema['tables']);
    }

    /**
     * Получение количества таблиц.
     * @return int
     */
    public function tablesCount(): int
    {
        $this->loadIfNotLoaded();
        return count($this->schema['tables']);
    }

    /**
     * Получение схемы таблицы.
     * @param string имя таблицы
     * @param bool|null сделать ли принудительное обновление схемы
     * @return array
     * @throws SchemaCacheException
     */
    public function table(string $table, bool $reload = false): array
    {
        if (true === $reload) $this->update();
        $this->loadIfNotLoaded();
        if (empty($this->schema['tables'][$table])) $this->update();
        if (empty($this->schema['tables'][$table])) {
            throw new SchemaCacheException(sprintf(
                'Database "%s"."%s" not has table "%s"',
                $this->db->host, $this->db->dbname, $table
            ));
        }
        return $this->schema['tables'][$table];
    }

    /**
     * Подгрузка кэша схемы из файла, если ещё не выолнялась.
     */
    protected function loadIfNotLoaded()
    {
        if (empty($this->schema['version'])) $this->load();
    }

    /**
     * Подгрузка кэша схемы из файла.
     */
    public function load()
    {
        if (is_file($this->filepath)) {
            $this->schema = include $this->filepath;
            $this->debug('received');
        }
        // обновляем, если версия хранения кэша схемы устарела
        if (floatval(@$this->schema['version'] ?? 0) < self::VERSION) {
            $this->update();
        }
    }

    /**
     * Обновление кэша схемы.
     */
    public function update()
    {
        $this->schema = [];
        $this->schema['version'] = self::VERSION;
        $tables = [];
        foreach ($this->db->grammar()->getTablesList() as $tableName) {
            $tables[$tableName] = $this->getTableSchema($tableName);
        }
        $this->schema['tables'] = $tables;
        // save
        $config = new PhpConfigRender($this->schema, $this->phpDoc());
        $dir = dirname($this->filepath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        file_put_contents($this->filepath, $config);
        $this->debug('updated');
    }

    /**
     * Очистка кэша схемы.
     */
    public function clear()
    {
        if (is_file($this->filepath)) {
            unlink($this->filepath);
            $this->debug('clear');
        } else {
            $this->debug('already_clear');
        }
    }

    /**
     * Получение схемы таблицы.
     * @param string имя таблицы
     * @return array
     */
    protected function getTableSchema(string $table): array
    {
        $columns = [];
        foreach ($this->db->grammar()->getTableColumns($table) as $column) {
            // $columns[$column['Field']] = new ColumnSchemaCache($column);
            $columns[] = $column['Field'];
        }
        return [
            'primaryKey' => $this->db->grammar()->getTablePrimaryKey($table), 
            'columns' => $columns, 
            // 'foreignKeys' => $this->db->grammar()->getForeignKeys($table),
            'foreignKeys' => [],
        ];
    }

    /**
     * PhpDoc для сохранения кэша схемы.
     * @return array
     */
    protected function phpDoc(): array
    {
        return [
            'Database schema cache.', 
            sprintf(
                'Host: %s; DbName: %s; Driver: %s.',
                $this->db->host, $this->db->dbname, $this->db->driver,
            ),
            '',
            'Generated by '. self::class,
            'on ' . date(\DATE_RFC7231),
            '',
            'Number of tables: ' . $this->tablesCount(),
        ];
    }

    /**
     * Дебаг.
     * @param string тип сообщения.
     */
    protected function debug(string $type)
    {
        evasDebug(sprintf(static::$debugMessages[$type], $this->filename()));
    }
}
