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
     * Оборачивание значения.
     * @param string значение
     * @return string обернутое значение
     */
    public function wrap(string $value): string
    {
        $value = $this->unwrap($value);
        return '*' === $value ? $value : "`$value`";
    }

    /**
     * Раскрытие оборачивания значения.
     * @param string обернутое значение
     * @return string значение
     */
    public function unwrap(string $value): string
    {
        return trim($value, '`');
    }

    /**
     * Оборачивание столбца.
     * @param string столбец
     * @return string обёрнутый столбец
     */
    public function wrapColumn(string $column): string
    {
        $parts = explode('.', $column);
        foreach ($parts as &$part) {
            $part = $this->wrap($part);
        }
        return implode('.', $parts);
    }

    /**
     * Оборачивание столбцов в готовые sql-столбцы.
     * @param array столбцы
     * @return string готовые sql-столбцами
     */
    public function wrapColumns(array $columns): string
    {
        foreach ($columns as &$column) {
            if (!strstr($column, 'AS'))
                $column = $this->wrapColumn($column);
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
