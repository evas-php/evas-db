<?php
/**
 * Грамматика PostreSQL.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars;

use Evas\Db\Grammars\AbstractGrammar;
use Evas\Db\Interfaces\GrammarInterface;

class PgsqlGrammar extends AbstractGrammar implements GrammarInterface
{
    // Оборачивание


    public function wrap(string $value): string
    {
        $value = $this->unwrap($value);
        return '*' === $value ? $value : "\"$value\"";
    }

    public function unwrap(string $value): string
    {
        return trim($value, '"');
    }


    // Настройка соединения

    /**
     * Установка таймзоны.
     * @param string кодировка
     */
    public function setTimezone(string $timezone)
    {
        return $this->query("set time zone '{$timezone}'");
    }


    // Кэш схемы

    public function getTablesList(): array
    {
        $tables = [];
        $rows = $this->db->query(
            'SELECT * FROM pg_catalog.pg_tables WHERE schemaname NOT IN (?,?)',
            ['pg_catalog', 'information_schema']
        )->assocArrayAll();
        foreach ($rows as &$row) {
            $tables[] = $row['tablename'];
        }
        return $tables;
    }

    public function getTablePrimaryKey(string $table): string
    {
        return $this->db->query(
            'SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS data_type
            FROM pg_index i
            JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
            WHERE i.indrelid = ?::regclass AND i.indisprimary'
            , [$table]
        )->assocArray()['attname'];
    }

    public function getTableColumns(string $table): array
    {
        $rows = $this->db->query(
            'SELECT * FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ?',
            ['public', $table]
        )->assocArrayAll();

        $columns = [];
        foreach ($rows as &$row) {
            $columns[] = [
                'Field' => $row['column_name'],
                'Null' => $row['is_nullable'],
                'Type' => $row['data_type'],
                'Default' => $row['column_default'],
            ];
        }
        return $columns;
    }

    public function getForeignKeys(string $table): array
    {
        $rows = $this->db->query('SELECT
            tc.table_schema, tc.constraint_name, 
            tc.table_name, kcu.column_name, 
            ccu.table_schema AS foreign_table_schema,
            ccu.table_name AS foreign_table_name,
            ccu.column_name AS foreign_column_name 
            FROM information_schema.table_constraints AS tc 
            JOIN information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name 
                AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
                ON ccu.constraint_name = tc.constraint_name 
                AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = ? AND tc.table_name = ?', 
            ['FOREIGN KEY', $table]
        )->assocArrayAll();

        $foreignKeys = [];
        foreach ($rows as &$row) {
            $foreignKeys[$row['column_name']] = [
                $row['foreign_table_schema'], 
                $row['foreign_table_name'], 
                $row['foreign_column_name']
            ];
        }
        return $foreignKeys;

    }


    // Сборка запросов
}
