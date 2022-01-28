<?php
/**
 * Mysql/Mariadb граматика сборки запросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars;

use Evas\Db\Grammars\Grammar;

class MysqlGrammar extends Grammar
{
    // protected function wrapJsonFieldAndPath(string $column): array
    // {
    //     $parts = explode('->', $column, 2);
    //     $field = $this->wrap($parts[0]);
    //     $path = count($parts) > 1 ? $this->wrapJsonPath($parts[1]) : '';
    //     return [$field, $path];
    // }

    // protected function wrapJsonPath(string $path): string
    // {
    //     // $parts = explode('->', $path);
    //     // // foreach ($parts as $part) {
    //     // //     // 
    //     // // }
    //     // return implode('.', $parts);
    //     return str_replace('->', '.', $path);
    // }
    

    protected function buildWhereJsonContains(array $where)
    {
        [$column, $path] = $this->parseJsonColumnAndPath($where['column']);
        return "{$this->getNot($where)} JSON_CONTAINS({$this->wrap($column)}, ?, {$path})";
    }

    protected function buildWhereJsonContainsPath(array $where)
    {
        [$column, $basePath] = $this->parseJsonColumnAndPath($where['column']);
        $paths = $where['paths'];
        if ($basePath) foreach ($paths as &$path) {
            $path = $this->wrapJsonPath($basePath . $path);
        }
        $paths = implode(', ', $paths);
        $find = $where['isOne'] ? 'one' : 'all';
        return "{$this->getNot($where)} JSON_CONTAINS_PATH({$this->wrap($column)}, '{$find}', {$paths})";
    }

    protected function buildWhereJsonLength(array $where)
    {
        [$column, $path] = $this->parseJsonColumnAndPath($where['column']);
        $parts = $this->wrap($column);
        if ($path) $parts .= ", $path";
        return "JSON_LENGTH({$parts})";
    }


    // ----------
    // Schema Cache
    // ----------

    public function getTablesList(): array
    {
        $tables = [];
        $rows = $this->db->query('SHOW TABLES')->numericArrayAll();
        foreach ($rows as &$row) {
            $tables[] = $row[0];
        }
        return $tables;
    }


    public function getTablePrimaryKey(string $table): ?string
    {
        return $this->db->query(
            "SHOW KEYS FROM {$this->wrapTable($table)} WHERE Key_name = 'PRIMARY'"
        )->assocArray()['Column_name'] ?? null;
    }

    public function getTableColumns(string $table): ?array
    {
        return $this->db->query(
            "SHOW COLUMNS FROM {$this->wrapTable($table)}"
        )->assocArrayAll();
    }

    public function getForeignKeys(string $table): ?array
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
}
