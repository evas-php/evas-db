<?php
/**
 * Абстрактный класс грамматики СУБД.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars;

use Evas\Db\Grammars\Traits\GrammarConnectionTrait;
use Evas\Db\Grammars\Traits\GrammarSchemaCacheTrait;
use Evas\Db\Grammars\Traits\GrammarQueryTrait;
use Evas\Db\Interfaces\DatabaseInterface;

abstract class AbstractGrammar
{
    // Настройка соединения
    use GrammarConnectionTrait;
    // Кэш схемы
    use GrammarSchemaCacheTrait;
    // Сборка запросов
    use GrammarQueryTrait;
    
    /** @var DatabaseInterface соединение с базой данных */
    protected $db;

    /**
     * Конструктор.
     * @param DatabaseInterface $db
     */
    public function __construct(DatabaseInterface &$db)
    {
        $this->db = &$db;
    }

    /**
     * Убирает оборачивание ключа (таблица/столбец).
     * @param string $key обернутый ключ
     * @return string ключ
     */
    public function unwrap(string $key): string
    {
        return str_replace('`', '', trim($key, '`'));
    }

    /**
     * Оборачивает ключ (таблица/столбец).
     * @param string $key ключ
     * @return string
     */
    public function wrap(string $key): string
    {
        if (stripos($key, ' AS ') !== false) {
            return $this->wrapAliased($key);
        }
        if ($this->isJsonSelector($key)) {
            return $this->wrapJsonSelector($key);
        }
        return implode('.', array_map([$this, 'wrapOne'], explode('.', $key)));
    }

    /**
     * Оборачивает сегмент ключа (таблица/столбец).
     * @param string $key ключ
     * @return string
     */
    public function wrapOne(string $key): string
    {
        $key = $this->unwrap($key);
        return '*' === $key ? $key : "`$key`";
    }

    /**
     * Оборачивает ключ (таблица/столбец) с алиасом (AS).
     * @param string $key ключ
     * @return string
     */
    protected function wrapAliased(string $key): string
    {
        $parts = preg_split('/\s+AS\s+/i', $key);
        return $this->wrap($parts[0]) . ' AS ' . $this->wrapOne($parts[1]);
    }

    /**
     * Проверка ключа (таблица/столбец) на то, является ли он json-ключом.
     * @param string $key ключ
     * @return bool
     */
    protected function isJsonSelector(string $key): bool
    {
        return strstr($key, '->');
    }

    /**
     * Оборачивает json-ключ (столбец).
     * @param string $key ключ
     * @return string
     */
    public function wrapJsonSelector(string $key): string
    {
        throw new \RuntimeException('This database does not support JSON operations');
    }

    /**
     * Оборачивает столбцы в готовые для sql столбцы в виде строки.
     * @param array|string $columns столбцы
     * @return string
     */
    public function wrapColumns(array|string $columns): string
    {
        if (is_string($columns)) {
            $columns = explode(',', str_replace(', ', ',', $columns));
        }
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    /**
     * Возвращает список индексов таблицы в едином формате.
     * @param string $table имя таблицы
     * @return array<string, array{type: string, columns: string[]}>
     */
    abstract public function getTableIndexes(string $table): array;

    /**
     * Возвращает индексы по колонкам и определяет их позиции в составных ключах.
     * @param string|array<string, array{type: string, columns: string[]}> $tableOrIndexes
     * @return array<string, array<string, array{type: string, columns: string[], position: int}>>
     */
    public function getIndexesByColumns(string|array $tableOrIndexes): array
    {
        $indexes = is_string($tableOrIndexes) ? $this->getTableIndexes($tableOrIndexes) : $tableOrIndexes;
        $columnIndexes = [];
        foreach ($indexes as $indexName => $indexData) {
            foreach ($indexData['columns'] as $pos => $colName) {
                $columnIndexes[$colName][$indexName] = [
                    'type'     => $indexData['type'],
                    'columns'  => $indexData['columns'],
                    'position' => $pos,
                ];
            }
        }
        return $columnIndexes;
    }

    /**
     * Возвращает мапу типов ключей колонки, отсортированную по приоритету (PRIMARY -> UNIQUE -> INDEX).
     * @param array<string, array{type: string, columns: string[], position: int}> $colIndexes
     * @return array<string, true>
     */
    protected function resolveColumnIndexTypes(array $colIndexes): array
    {
        if (empty($colIndexes)) return [];

        // Задаем эталонный порядок приоритетов
        $found = [];
        foreach ($colIndexes as $idxInfo) {
            $found[$idxInfo['type']] = true;
        }

        $orderedKeys = [];
        foreach (['PRIMARY', 'UNIQUE', 'INDEX'] as $priorityType) {
            if (isset($found[$priorityType])) {
                $orderedKeys[$priorityType] = true;
            }
        }
        return $orderedKeys;
    }

}
