<?php
namespace Evas\Db\Builders\Options;

use Evas\Db\Builders\Options\AbstractOption;

abstract class AbstractWhereOrHavingOption extends AbstractOption
{
    /** @var array доступные операторы */
    public static $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'LIKE', 'LIKE BINARY', 'NOT LIKE', 'ILIKE',
        '&', '|', '^', '<<', '>>', '&~',
        'RLIKE', 'NOT RLIKE', 'REGEXP', 'NOT REGEXP',
        '~', '~*', '!~', '!~*', 'SIMILAR TO',
        'NOT SIMILAR TO', 'NOT ILIKE', '~~*', '!~~*',
    ];
    
    public $type;
    public $isOr = false;
    public $isNot = false;

    public $sql;
    public $bindings;

    public $column;
    public $columns;
    public $operator;

    public $function;

    protected static function createStaticWithBindings(string $type, array $props)
    {
        if (!empty($props['values'])) {
            $props['bindings'] = $props['values'];
            unset($props['values']);
        }
        if (isset($props['value'])) {
            $props['bindings'] = [$props['value']];
            unset($props['value']);
        }
        return new static($type, $props);
    }

    public static function raw(string $sql, array $bindings = [], bool $isOr = false)
    {
        return new static('Raw', compact('sql', 'bindings', 'isOr'));
    }

    public static function null($column, bool $isOr = false, bool $isNot = false)
    {
        if (is_string($column)) {
            return new static('Null', compact('column', 'isOr', 'isNot'));
        } else if (is_array($column)) {
            $options = [];
            foreach ($column as $sub) {
                $options[] = static::null($sub, $isOr, $isNot);
            }
            return $options;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an array or a string, %s given',
                __METHOD__, gettype($columns)
            ));
        }
    }

    public static function between(
        string $column, array $values, bool $isOr = false, bool $isNot = false
    ) {
        $values = array_slice($values, 0, 2);
        return static::createStaticWithBindings(
            'Between', compact('column', 'values', 'isOr', 'isNot'
        );
    }

    public static function betweenColumns(
        string $column, array $columns, bool $isOr = false, bool $isNot = false
    ) {
        $columns = array_slice($columns, 0, 2);
        return new static('BetweenColumns', compact('column', 'columns', 'isOr', 'isNot');
    }


    // Вспомогательные методы

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
