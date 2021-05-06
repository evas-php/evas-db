<?php
/**
 * Класс схемы колонки.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Schema;

class ColumnSchema
{
    /** @var string имя */
    public $name;

    /** @var string тип */
    public $type;

    /** @var bool разрешен ли NULL */
    public $null;

    /** @var string|null ключ */
    public $key;

    /** @var string|null значение по умолчанию */
    public $default;

    /** @var string|null extra */
    public $extra;

    /**
     * Конструктор.
     * @param array параметры колонки
     */
    public function __construct(array $params = null)
    {
        if ($params) foreach ($params as $name => $value) {
            $this->__set($name, $value);
        }
    }

    public function __set(string $name, $value)
    {
        if ('Field' === $name) $name = 'name';
        else if ('Null' === $name) $value = 'YES' == $value ? true : false;
        $name = strtolower($name);
        $this->$name = $value;
    }

    /**
     * Проверка на индекс.
     * @return bool
     */
    public function isIndex(): bool
    {
        return !empty($this->key) ? true : false;
    }
}
