<?php
/**
 * Трейт грамматики запросов СУБД для настройки соединения.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars\Traits;

trait GrammarConnectionTrait
{
    /**
     * Установка таймзоны.
     * @param string кодировка
     */
    abstract public function setTimezone(string $timezone);

    /**
     * Установка кодировки.
     * @param string кодировка
     */
    public function setCharset(string $charset)
    {
        return $this->db->query("SET NAMES '{$charset}'");
    }

    /**
     * Переключение базы данных.
     * @param string имя базы данных
     */
    public function changeDbName(string $dbname)
    {
        return $this->db->query("USE {$this->wrap($dbname)}");
    }
}
