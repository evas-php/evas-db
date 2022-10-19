<?php
/**
 * Трейт обёртки столбцов и таблиц.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WrapsTrait
{
    /**
     * Раскрытие оборачивания ключа (таблица/столбец).
     * @param string обернутый ключ
     * @return string ключ
     */
    public function unwrap(string $key): string
    {
        return $this->db->grammar()->unwrap($key);
    }

    /**
     * Оборачивание ключа (таблица/столбец).
     * @param string ключ
     * @return string обернутый ключ
     */
    public function wrap(string $key): string
    {
        return $this->db->grammar()->wrap($key);
    }

    /**
     * Оборачивание сегмента ключа (таблица/столбец).
     * @param string ключ
     * @return string обернутый ключ
     */
    public function wrapOne(string $key): string
    {
        return $this->db->grammar()->wrapOne($key);
    }
}
