<?php
/**
 * Кэш схемы колонки.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\SchemaCache;

class ColumnSchemaCache
{
    /** @var string имя */
    public $name;
    /** @var string тип */
    public $type;
    /** @var string точность, например, 6 для datetime(6) или 2,10 для decimal(2,10) */
    public $length;
    /** @var string оригинальный тип от БД */
    public $dbType;
    /** @var bool разрешен ли NULL */
    public $null;
    /** @var string|null значение по умолчанию */
    public $default;
    /** @var array extra */
    public $extra = [];
    /** @var string|null оригинальный extra от БД */
    public $dbExtra;
    /** @var array типы индексов */
    public $indexTypes = [];
    /** @var array индексы */
    public $indexes = [];


    /**
     * Конструктор.
     * @param array $params параметры колонки
     */
    public function __construct(?array $params = null)
    {
        if ($params) foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Приведение к строке.
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * Проверка на индекс.
     * @return bool
     */
    public function isIndex(): bool
    {
        return count($this->indexTypes) > 0;
    }

    /**
     * Является ли колонка первичным ключом.
     * @return bool
     */
    public function isPrimary(): bool
    {
        return isset($this->indexTypes['PRIMARY']);
    }

    /**
     * Являются ли значения колонки уникальными.
     * @return bool
     */
    public function isUnique(): bool
    {
        return isset($this->indexTypes['UNIQUE']);
    }

    /**
     * Является ли колонка индексной.
     * @return bool
     */
    public function isIndexed(): bool
    {
        return isset($this->indexTypes['INDEX']);
    }

    /**
     * Установлен ли auto_increment для колнки.
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return isset($this->extra['auto_increment']);
    }

    /**
     * Является ли значение колонки генерируемым на основе значений других колонок.
     * @return bool
     */
    public function isGenerated(): bool
    {
        return isset($this->extra['generated']);
    }

    /**
     * Является ли значение колонки обновляемым на текущую дату и время.
     * @return bool
     */
    public function isOnUpdate(): bool
    {
        return isset($this->extra['on_update']);
    }

    /**
     * Является ли значение колонки unsigned.
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return isset($this->extra['unsigned']);
    }
}
