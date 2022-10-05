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
     * @param DatabaseInterface
     */
    public function __construct(DatabaseInterface &$db)
    {
        $this->db = &$db;
    }

    /**
     * Раскрытие оборачивания ключа (таблица/столбец).
     * @param string обернутый ключ
     * @return string ключ
     */
    public function unwrap(string $key): string
    {
        return str_replace('`', '', trim($key, '`'));
    }

    /**
     * Оборачивание ключа (таблица/столбец).
     * @param string ключ
     * @return string обернутый ключ
     */
    public function wrap(string $key): string
    {
        if (stripos($key, ' AS ') !== false) {
            return $this->wrapAliased($key);
        }
        if ($this->isJsonSelector($key)) {
            return $this->wrapJsonSelector($key);
        }

        // $parts = explode('.', $key);
        // foreach ($parts as &$part) {
        //     $part = $this->wrap($part);
        // }
        // return implode('.', $parts);
        return implode('.', array_map([$this, 'wrapOne'], explode('.', $key)));
    }

    /**
     * Оборачивание сегмента ключа (таблица/столбец).
     * @param string ключ
     * @return string обернутый ключ
     */
    public function wrapOne(string $key): string
    {
        $key = $this->unwrap($key);
        return '*' === $key ? $key : "`$key`";
    }

    /**
     * Оборачивание ключа (таблица/столбец) с алиасом (AS).
     * @param string ключ
     * @return string обернутый ключ
     */
    public function wrapAliased(string $key): string
    {
        $parts = preg_split('/\s+AS\s+/i', $key);
        return $this->wrap($parts[0]) . ' AS ' . $this->wrapOne($parts[1]);
    }

    /**
     * Проверка ключа (таблица/столбец) на то, является ли он json-ключом.
     * @param string ключ
     * @return bool
     */
    protected function isJsonSelector(string $key): bool
    {
        return strstr($key, '->');
    }

    /**
     * Оборачивание json-ключа (столбец).
     * @param string ключ
     * @return string обернутый ключ
     */
    public function wrapJsonSelector(string $key): string
    {
        throw new \RuntimeException('This database does not support JSON operations');
    }

    /**
     * Оборачивание столбцов в готовые sql-столбцы.
     * @param array столбцы
     * @return string готовые sql-столбцами
     */
    public function wrapColumns(array $columns): string
    {
        foreach ($columns as &$column) {
            
            foreach ($parts as &$part) {
                $part = $this->wrap($column);
            }
            if (!strstr($column, 'AS'))
                $column = $this->wrap($column);
        }
        return implode(', ', $columns);
    }

    /**
     * Оборачивание столбцов переданных строкой в готовые sql-столбцы.
     * @param array столбцы в виде строки
     * @return string готовые sql-столбцами
     */
    public function wrapStringColumns(string $value): string
    {
        $columns = explode(',', str_replace(', ', ',', $value));
        return $this->wrapColumns($columns);
    }
}
