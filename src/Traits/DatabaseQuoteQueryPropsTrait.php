<?php
/**
 * Трейт экранирования параметров запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

// if (!defined('EVAS_DB_QUOTE_ARRAY_OR_OBJECT_CALLBACK')) {
//     define('EVAS_DB_QUOTE_ARRAY_OR_OBJECT_CALLBACK', function ($value) {
//         return json_encode($value, JSON_UNESCAPED_UNICODE);
//     });
// }

trait DatabaseQuoteQueryPropsTrait
{
    // /** @static array доступные функции экранирования объектов */
    // const QUOTE_OBJECTS_FUNCS = [
    //     'null' => '\'NULL\'; intval', 
    //     'json' => '\json_encode',
    //     'serialize' => '\serialize', 
    // ];

    // /** @var string имя функции для экранирования объектов и массивов */
    // protected $quoteObjectsFunc = self::QUOTE_OBJECTS_FUNCS['json'];

    /** @var callable колбек экранирования массива или объекта в параметре запроса. */
    private $quoteArrayOrObjectCallback;

    /**
     * Установка/сброс колбека экранирования массива или объекта в параметре запроса.
     * @param callable|null колбек
     * @return self
     */
    public function setQuoteArrayOrObjectCallback(callable $callback = null)
    {
        $this->quoteArrayOrObjectCallback = $callback;
        return $this;
    }

    /**
     * Экранирование параметра запроса.
     * @param mixed значение
     * @return string|numeric|null экранированное значение
     */
    public function quote($value)
    {
        return $value instanceof \Closure ? null : $this->quoteArrayOrObject($value);
    }

    /**
     * Экранирование массива или объекта в параметре запроса.
     * @param mixed значение
     * @return string|numeric|null экранированное значение
     */
    public function quoteArrayOrObject($value)
    {
        if (is_array($value) || is_object($value)) {
            return empty($this->quoteArrayOrObjectCallback) ? null
            : $this->quoteArrayOrObjectCallback($value);
        }
        return $value;
    }
}
