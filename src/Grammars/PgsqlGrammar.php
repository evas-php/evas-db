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

    /**
     * Убирает оборачивание ключа (таблица/столбец).
     * @param string $key обернутый ключ
     * @return string
     */
    public function unwrap(string $key): string
    {
        return str_replace('"', '', trim($key, '"'));
    }

    /**
     * Оборачивает сегмент ключа (таблица/столбец).
     * @param string $key ключ
     * @return string
     */
    public function wrapOne(string $key): string
    {
        $key = $this->unwrap($key);
        return '*' === $key ? $key : "\"$key\"";
    }


    // Настройка соединения

    /**
     * Установка таймзоны.
     * @param string $timezone кодировка
     */
    public function setTimezone(string $timezone)
    {
        return $this->db->query("set time zone '{$timezone}'");
    }


    // Кэш схемы

    /**
     * Возвращает спискок таблиц.
     * @return array
     */
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

    /**
     * Возвращает первичный ключ таблицы.
     * @param string $table имя таблицы
     * @return string
     */
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
        $rows = $this->db->query(
            'SELECT column_name, data_type, udt_name, is_nullable, column_default,
                    character_maximum_length, numeric_precision, numeric_scale, datetime_precision,
                    is_identity, is_generated
            FROM information_schema.columns
            WHERE table_schema = ? AND table_name = ?
            ORDER BY ordinal_position',
            ['public', $table]
        )->assocArrayAll();

        $indexesByColumns = $this->getIndexesByColumns($table);
        $unsignedColumns = $this->getUnsignedColumns($table);
        $columns = [];

        foreach ($rows as $row) {
            $name = $row['column_name'];
            $dbType = $row['data_type'];
            $rawType = strtolower($dbType);

            $type = match ($rawType) {
                'timestamp without time zone', 'timestamp with time zone' => 'timestamp',
                'time without time zone', 'time with time zone'           => 'time',
                'character varying', 'character'                          => 'varchar',
                'boolean'                                                 => 'bool',
                'integer'                                                 => 'int',
                'jsonb'                                                   => 'json',
                'double precision'                                        => 'double',
                default                                                   => $rawType,
            };

            $length = $this->resolveColumnLength($row, $type);

            $default = $row['column_default'];
            $isSerial = is_string($default) && str_starts_with($default, 'nextval(');
            $isIdentity = strtoupper($row['is_identity'] ?? 'NO') === 'YES';
            $isGenerated = strtoupper($row['is_generated'] ?? 'NEVER') !== 'NEVER';
            
            $extra = [];
            $dbExtraParts = [];
            
            if ($isSerial || $isIdentity) {
                $extra['auto_increment'] = true;
                $dbExtraParts[] = $isIdentity ? 'IDENTITY' : 'SERIAL';
            }
            if ($isGenerated) {
                $extra['generated'] = true;
                $dbExtraParts[] = 'GENERATED';
            }
            if (isset($unsignedColumns[$name])) {
                $extra['unsigned'] = true;
            }

            $colIndexes = $indexesByColumns[$name] ?? [];
            $columns[] = [
                'name'       => $name,
                'type'       => $type,
                'dbType'     => $dbType,
                'length'     => $length !== null ? (string) $length : null,
                'null'       => strtoupper($row['is_nullable']) === 'YES',
                'default'    => ($isSerial || $isIdentity) ? null : $default,
                'indexTypes' => $this->resolveColumnIndexTypes($colIndexes),
                'indexes'    => $colIndexes,
                'extra'      => $extra,
                'dbExtra'    => implode(' ', $dbExtraParts),
            ];
        }

        return $columns;
    }

    /**
     * Извлекает длину или точность столбца из строки information_schema.columns.
     * @param array<string, mixed> $row
     * @param string $type Нормализованный тип столбца.
     * @return string|null
     */
    private function resolveColumnLength(array $row, string $type): ?string
    {
        $dtPrecision = $row['datetime_precision'] !== null ? (int) $row['datetime_precision'] : null;
        $numPrecision = $row['numeric_precision'];
        $numScale = $row['numeric_scale'];
        return match (true) {
            $row['character_maximum_length'] !== null => (string) $row['character_maximum_length'],
            $dtPrecision !== null && $type !== 'date' && $dtPrecision !== 6 => (string) $dtPrecision,
            $numPrecision !== null => $numScale ? "{$numPrecision},{$numScale}" : (string) $numPrecision,
            default => null,
        };
    }

    /**
     * Возвращает мапу колонок PostgreSQL, имеющих CHECK-ограничение на неотрицательность (>= 0 или > 0).
     * @param string $table
     * @return array<string, true>
     */
    private function getUnsignedColumns(string $table): array
    {
        $rows = $this->db->query(
            'SELECT
                a.attname AS column_name,
                pg_get_constraintdef(c.oid) AS definition
            FROM pg_constraint c
            JOIN pg_class t ON t.oid = c.conrelid
            JOIN pg_namespace n ON n.oid = t.relnamespace
            JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = c.conkey
            WHERE n.nspname = ?
            AND t.relname = ?
            AND c.contype = \'c\'
            AND array_length(c.conkey, 1) = 1',
            ['public', $table]
        )->assocArrayAll();

        $unsigned = [];
        foreach ($rows as $row) {
            $col = $row['column_name'];
            $def = strtolower($row['definition']);
            // Проверяем типовые выражения PostgreSQL: CHECK ((age >= 0)) или CHECK ((age > 0))
            $pattern = '/\(\s*"?' . preg_quote($col, '/') . '"?\s*>=?\s*0\s*\)/';
            if (preg_match($pattern, $def)) {
                $unsigned[$col] = true;
            }
        }
        return $unsigned;
    }


    /**
     * Возвращает список индексов таблицы в едином формате.
     * @param string $table имя таблицы
     * @return array<string, array{type: string, columns: string[]}>
     */
    public function getTableIndexes(string $table): array
    {
        $indexRows = $this->db->query(
            'SELECT
                i.relname AS index_name,
                ix.indisprimary AS is_primary,
                ix.indisunique AS is_unique,
                a.attname AS column_name,
                array_position(ix.indkey, a.attnum) AS column_pos
            FROM pg_class t
            JOIN pg_namespace n ON n.oid = t.relnamespace
            JOIN pg_index ix ON t.oid = ix.indrelid
            JOIN pg_class i ON i.oid = ix.indexrelid
            JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(ix.indkey)
            WHERE n.nspname = ? AND t.relname = ? AND t.relkind = \'r\'
            ORDER BY i.relname, column_pos',
            ['public', $table]
        )->assocArrayAll();

        $indexes = [];
        foreach ($indexRows as $idx) {
            $indexName = $idx['index_name'];
            if (!isset($indexes[$indexName])) {
                $type = match (true) {
                    (bool) $idx['is_primary'] => 'PRIMARY',
                    (bool) $idx['is_unique']  => 'UNIQUE',
                    default                   => 'INDEX',
                };
                $indexes[$indexName] = [
                    'type'    => $type,
                    'columns' => [],
                ];
            }
            $indexes[$indexName]['columns'][] = $idx['column_name'];
        }

        return $indexes;
    }



    /**
     * Возвращает внешние ключи таблицы.
     * @param string $table имя таблицы
     * @return array<string, array{schema: string, table: string, column: string}>
     */
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
                'schema' => $row['foreign_table_schema'], 
                'table'  => $row['foreign_table_name'], 
                'column' => $row['foreign_column_name']
            ];
        }
        return $foreignKeys;

    }


    // Сборка запросов
}
