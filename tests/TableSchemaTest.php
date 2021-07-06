<?php
/**
 * Тест маппинга идентичности сущностей данных.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests;

use Codeception\Util\Autoload;
use Evas\Db\Base\QueryResult;
use Evas\Db\Database;
use Evas\Db\IdentityMap;
use Evas\Db\Schema\ColumnSchema;
use Evas\Db\Schema\TableSchema;
use Evas\Db\tests\help\DatabaseTestUnit;

Autoload::addNamespace('Evas\\Db', 'vendor/evas-php/evas-db/src');
Autoload::addNamespace('Evas\\Db\\tests\\help', 'vendor/evas-php/evas-db/src/tests/help');

class TableSchemaTest extends DatabaseTestUnit
{
    // Вспомогательные свойства и методы

    public static $tableName = 'users';
    protected static $tableSchemas = [];
    protected static $columnSchemas;

    public static function tableSchema(string $tableName = null)
    {
        if (empty($tableName)) $tableName = static::$tableName;
        if (empty(static::$tableSchemas[$tableName])) {
            $db = static::staticDb();
            static::$tableSchemas[$tableName] = new TableSchema($db, $tableName);
        }
        return static::$tableSchemas[$tableName];
    }

    protected static function getFullName()
    {
        $dbname = static::staticDb()->dbname;
        $tableName = static::$tableName;
        return "`$dbname`.`$tableName`";
    }

    protected static function columnSchemas()
    {
        if (!static::$columnSchemas) {
            $from = static::getFullName();
            $rows = static::staticDb()->query("SHOW COLUMNS FROM $from")->assocArrayAll();
            static::$columnSchemas = [];
            if ($rows) foreach ($rows as &$row) {
                static::$columnSchemas[$row['Field']] = (array) new ColumnSchema($row);
            }
        }
        return static::$columnSchemas;
    }

    protected static function foreignKeys(string $tableName = null) {
        if (empty($tableName)) $tableName = static::$tableName;
        $sql = 'SELECT * FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME <>"PRIMARY" 
            AND REFERENCED_TABLE_NAME IS NOT NULL';
        $values = [static::staticDb()->dbname, $tableName];
        $rows = static::staticDb()->query($sql, $values)->assocArrayAll();
        $foreignKeys = [];
        if ($rows) foreach ($rows as &$row) {
            $foreignKeys[$row['COLUMN_NAME']] = [
                $row['REFERENCED_TABLE_SCHEMA'], 
                $row['REFERENCED_TABLE_NAME'], 
                $row['REFERENCED_COLUMN_NAME']
            ];
        }
        return $foreignKeys;
    }

    // Тесты

    /**
     * Тест получения полного имя таблицы.
     */
    public function testFullName()
    {
        $this->assertEquals(static::getFullName(), static::tableSchema()->fullName());
    }

    /**
     * Тест получения первичного ключа таблицы.
     */
    public function testPrimaryKey()
    {
        $this->assertEquals('id', static::tableSchema()->primaryKey());
        $this->assertEquals('id', static::tableSchema()->primaryKey(true));
    }

    /**
     * Тест получения полного схем столбцов таблицы.
     */
    public function testColumnSchemas()
    { 
        
        $this->assertEquals(static::columnSchemas(), static::tableSchema()->columnSchemas());
        $this->assertEquals(static::columnSchemas(), static::tableSchema()->columnSchemas(true));
    }

    /**
     * Тест получения схемы столбца таблицы.
     */
    public function testColumnSchema()
    {
        $expected = new ColumnSchema(static::columnSchemas()['id']);
        $this->assertEquals($expected, static::tableSchema()->columnSchema('id'));
        $this->assertEquals($expected, static::tableSchema()->columnSchema('id', true));
    }

    /**
     * Тест получения столбцов таблицы.
     */
    public function testColumns()
    {
        $expected = array_keys(static::columnSchemas());
        $this->assertEquals($expected, static::TableSchema()->columns());
        $this->assertEquals($expected, static::TableSchema()->columns(true));
    }

    /**
     * Тест получения внешних ключей таблицы.
     */
    public function testForeignKeys()
    {
        $this->assertEmpty(static::tableSchema()->foreignKeys());

        $expected = static::foreignKeys('auths');
        $this->assertEquals($expected, static::tableSchema('auths')->foreignKeys());
        $this->assertEquals($expected, static::tableSchema('auths')->foreignKeys(true));

    }
}
