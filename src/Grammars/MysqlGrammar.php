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
     * @param string $timezone кодировка
     */
    public function setTimezone(string $timezone)
    {
        return $this->db->query("set time_zone='{$timezone}'");
    }


    // Кэш схемы

    /**
     * Возвращает спискок таблиц.
     * @return array
     */
    public function getTablesList(): array
    {
        $tables = [];
        $rows = $this->db->query('SHOW TABLES')->numericArrayAll();
        foreach ($rows as &$row) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    /**
     * Возвращает первичный ключ таблицы.
     * @param string $table имя таблицы
     * @return string
     */
    public function getTablePrimaryKey(string $table): string
    {
        return $this->db->query(
            "SHOW KEYS FROM {$this->wrapOne($table)} WHERE Key_name = 'PRIMARY'"
        )->assocArray()['Column_name'] ?? null;
    }

    /**
     * Возвращает столбцы таблицы в едином формате.
     * @param string $table имя таблицы
     * @return array<string, array{
     *     name: string, 
     *     type: string, 
     *     length: string, 
     *     dbType: string, 
     *     null: bool, 
     *     default: string,
     *     key: string, 
     *     indexes: array{schema: string, table: string, column: string}, 
     *     extra: array<string, bool>,
     *     dbExtra: string
     * }>
     */
    public function getTableColumns(string $table): array
    {
        $rows = $this->db->query("SHOW COLUMNS FROM {$this->wrapOne($table)}")->assocArrayAll();
        $indexesByColumns = $this->getIndexesByColumns($table);
        $columns = [];

        foreach ($rows as $row) {
            $dbType = $row['Type'];
            $rawType = strtolower($dbType);
            $type = $rawType;
            $length = null;

            if (preg_match('/^([a-z]+)(?:\(([^)]+)\))?/', $rawType, $matches)) {
                $type = $matches[1];
                $length = $matches[2] ?? null;
            }
            if ($type === 'tinyint' && $length === '1') $type = 'bool';

            $dbExtra = $row['Extra'] ?? '';
            $rawExtra = strtolower($dbExtra);
            $extra = [];

            if (str_contains($rawExtra, 'auto_increment')) {
                $extra['auto_increment'] = true;
            }
            if (str_contains($rawExtra, 'on update current_timestamp')) {
                $extra['on_update'] = true;
            }
            if (str_contains($rawExtra, 'generated')) {
                $extra['generated'] = true;
            }
            if (str_contains($rawType, 'unsigned')) {
                $extra['unsigned'] = true;
            }

            $name = $row['Field'];
            $colIndexes = $indexesByColumns[$name] ?? [];

            $columns[] = [
                'name'       => $name,
                'type'       => $type,
                'dbType'     => $dbType,
                'length'     => $length,
                'null'       => strtoupper($row['Null']) === 'YES',
                'default'    => $row['Default'],
                'indexTypes' => $this->resolveColumnIndexTypes($colIndexes),
                'indexes'    => $colIndexes,
                'extra'      => $extra,
                'dbExtra'    => $dbExtra,
            ];
        }

        return $columns;
    }


    /**
     * Возвращает список индексов таблицы в едином формате.
     * @param string $table имя таблицы
     * @return array<string, array{type: string, columns: string[]}>
     */
    public function getTableIndexes(string $table): array
    {
        $indexRows = $this->db->query("SHOW INDEX FROM {$this->wrapOne($table)}")->assocArrayAll();
        $indexes = [];
        foreach ($indexRows as $idx) {
            $indexName = $idx['Key_name'];
            $seq = (int) $idx['Seq_in_index'];

            if (!isset($indexes[$indexName])) {
                $type = match (true) {
                    $indexName === 'PRIMARY'       => 'PRIMARY',
                    (int) $idx['Non_unique'] === 0 => 'UNIQUE',
                    default                        => 'INDEX',
                };
                $indexes[$indexName] = [
                    'type'    => $type,
                    'columns' => [],
                ];
            }

            $indexes[$indexName]['columns'][$seq] = $idx['Column_name'];
        }

        foreach ($indexes as &$indexData) {
            ksort($indexData['columns']);
            $indexData['columns'] = array_values($indexData['columns']);
        }
        unset($indexData);

        return $indexes;
    }


    /**
     * Возвращает внешние ключи таблицы.
     * @param string $table имя таблицы
     * @return array<string, array{schema: string, table: string, column: string}>
     */
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
                'schema' => $row['REFERENCED_TABLE_SCHEMA'], 
                'table'  => $row['REFERENCED_TABLE_NAME'], 
                'column' => $row['REFERENCED_COLUMN_NAME']
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
