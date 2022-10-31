<?php
/**
 * Трейт подготовки данных для сборки запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait PrepareTrait
{
    /**
     * Подготовка значения и оператора.
     * Для методов, в которых можно опутить оператор сравения - "=".
     * @param mixed значение
     * @param mixed оператор
     * @param bool|null использовать ли оператор в качестве значения
     * @return array [значение, оператор]
     * @throws \InvalidArgumentException
     */
    protected static function prepareValueAndOperator(
        &$value, &$operator, bool $useDefault = false
    ) {
        if ($useDefault) {
            $value = $operator;
            $operator = '=';
            // return [$operator, '='];
        } else if (static::invalidValueAndOperator($value, $operator)) {
            throw new \InvalidArgumentException(json_encode([
                'error' => 'Illegal operator and value combination.',
                'operator' => $operator,
                'value' => $value,
            ]));
        }
        // return [$value, $operator];
    }

    /**
     * Проверка на неправильность значения и оператора.
     * @param mixed значение
     * @param mixed оператор
     * @return bool
     */
    protected static function invalidValueAndOperator($value, $operator): bool
    {
        /**
         * @todo Проверять операторы конкретной СУБД
         */
        return is_null($value) && in_array($operator, static::$operators) 
        && !in_array($operator, ['=', '<>', '!=']);
    }
}
