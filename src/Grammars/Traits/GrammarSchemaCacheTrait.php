<?php
/**
 * Трейт грамматики запросов СУБД для кэша схемы БД.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars\Traits;

trait GrammarSchemaCacheTrait
{
    /**
     * Получение списка таблиц.
     */
    public function getTablesList(): array
    {
        return [];
    }

    /**
     * Получение первичного ключа таблицы.
     * @param string имя таблицы
     */
    public function getTablePrimaryKey(string $table): ?string
    {
        return 'id';
    }

    /**
     * Получение столбцов таблицы.
     * @param string имя таблицы
     */
    public function getTableColumns(string $table)
    {
        return [];
    }

    /**
     * Получение внешних ключей таблицы.
     * @param string имя таблицы
     */
    public function getForeignKeys(string $table)
    {
        return [];
    }
}
