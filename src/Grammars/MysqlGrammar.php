<?php
/**
 * Грамматика MySQL/MariaDB.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars;

use Evas\Db\Grammars\AbstractGrammar;
use Evas\Db\Interfaces\GrammarInterface;

class MysqlGrammar extends AbstractGrammar implements GrammarInterface
{
    // Настройка соединения

    /**
     * Установка таймзоны.
     * @param string кодировка
     */
    public function setTimezone(string $timezone)
    {
        return $this->query("set time_zone='{$timezone}'");
    }


    // Кэш схемы

    public function getTablesList(): array
    {
        $tables = [];
        $rows = $this->db->query('SHOW TABLES')->numericArrayAll();
        foreach ($rows as &$row) {
            $tables[] = $row[0];
        }
        return $tables;
    }


    public function getTablePrimaryKey(string $table): string
    {
        return $this->db->query(
            "SHOW KEYS FROM {$this->wrap($table)} WHERE Key_name = 'PRIMARY'"
        )->assocArray()['Column_name'] ?? null;
    }

    public function getTableColumns(string $table): array
    {
        return $this->db->query("SHOW COLUMNS FROM {$this->wrap($table)}")
        ->assocArrayAll();
    }

    public function getForeignKeys(string $table): array
    {
        $rows = $this->db->query(
            'SELECT * FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME <>"PRIMARY" AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$this->db->dbname, $table]
        )->assocArrayAll();

        $foreignKeys = [];
        foreach ($rows as &$row) {
            $foreignKeys[$row['COLUMN_NAME']] = [
                $row['REFERENCED_TABLE_SCHEMA'], 
                $row['REFERENCED_TABLE_NAME'], 
                $row['REFERENCED_COLUMN_NAME']
            ];
        }
        return $foreignKeys;
    }


    // Сборка запросов

    // protected function buildWhereJsonContains(array $where)
    // {
    //     [$column, $path] = $this->parseJsonColumnAndPath($where['column']);
    //     return "{$this->getNot($where)} JSON_CONTAINS({$this->wrapColumn($column)}, ?, {$path})";
    // }

    // protected function buildWhereJsonContainsPath(array $where)
    // {
    //     [$column, $basePath] = $this->parseJsonColumnAndPath($where['column']);
    //     $paths = $where['paths'];
    //     if ($basePath) foreach ($paths as &$path) {
    //         $path = $this->wrapJsonPath($basePath . $path);
    //     }
    //     $paths = implode(', ', $paths);
    //     $find = $where['isOne'] ? 'one' : 'all';
    //     return "{$this->getNot($where)} JSON_CONTAINS_PATH({$this->wrapColumn($column)}, '{$find}', {$paths})";
    // }

    // protected function buildWhereJsonLength(array $where)
    // {
    //     [$column, $path] = $this->parseJsonColumnAndPath($where['column']);
    //     $parts = $this->wrapColumn($column);
    //     if ($path) $parts .= ", $path";
    //     return "JSON_LENGTH({$parts})";
    // }
}
