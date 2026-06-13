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
     * @return array таблицы
     */
    public function getTablesList(): array
    {
        return [];
    }

    /**
     * Получение первичного ключа таблицы.
     * @param string имя таблицы
     * @return string первичный ключ
     */
    public function getTablePrimaryKey(string $table): string
    {
        return 'id';
    }

    /**
     * Получение столбцов таблицы.
     * @param string имя таблицы
     * @return array столбцы
     */
    public function getTableColumns(string $table): array
    {
        return [];
    }

    /**
     * Получение внешних ключей таблицы.
     * @param string имя таблицы
     * @return array внешние ключи
     */
    public function getForeignKeys(string $table): array
    {
        return [];
    }
}
