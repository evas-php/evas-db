<?php
/**
 * Интерфейс кэша схемы базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

interface SchemaCacheInterface
{
    /**
     * Получение списка таблиц.
     * @param bool|null сделать ли принудительное обновление схемы
     * @return array
     */
    public function tablesList(bool $reload = false): array;

    /**
     * Получение схемы таблицы.
     * @param string имя таблицы
     * @param bool|null сделать ли принудительное обновление схемы
     * @return array
     */
    public function table(string $table, bool $reload = false): array;
}
