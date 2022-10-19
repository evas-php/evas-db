<?php
namespace Evas\Db\Builders\Options;

use Evas\Db\Builders\Options\AbstractWhereOrHavingOption;
use Evas\Db\Builders\Options\Traits\IsQueryableTrait;

class WhereOption extends AbstractWhereOrHavingOption
{
    use IsQueryableTrait;
    
    // const TYPES = [
    //     'Raw', 'Single', 'SingleColumn', 'Nested', 
    //     'Sub', 'Null', 'In', 'Exists', 
    //     'Between', 'BetweenColumns', 'DateBased', 'BetweenDateBased',
    //     'RowValues',
    // ];

    public $to = 'wheres';

    public $queryable;

    public static function single(bool $isOr, $column, $operator = null, $value = null)
    {
        // массив условий
        if (is_array($column)) {
            $options = [];
            foreach ($column as $col => $val) {
                if (is_array($val)) {
                    $vals = array_values($val);
                    if (!is_numeric($col)) array_unshift($vals, $col);
                } else {
                    $vals = [$col, '=', $val];
                }
                // $this->addSingleWhere($isOr, ...$vals);
                $options[] = static::single($isOr, ...$vals);
            }
            return $options;
        }

        // подготовка значения и оператора условия
        // [$value, $operator] = 
        static::prepareValueAndOperator(
            $value, $operator, func_num_args() === 3
        );

        if (static::isQueryable($column)) {
            return is_null($operator)
            // Raw запрос через queryable
            ? static::nested($column, $isOr)
            // подзапрос столбца
            : static::subColumn($column, $operator, $value, $isOr);

        }

        // IS NULL условие
        if (is_null($value)) {
            return static::null($column, $isOr, $operator !== '=');
        }

        // подзапрос значения
        if (static::isQueryable($value)) {
            return static::sub($column, $operator, $value, $isOr);
        }

        // single условие
        return static::createStaticWithBindings(
            'Single', compact('column', 'operator', 'value', 'isOr')
        );
    }

    public static function singleColumn(bool $isOr, $first, $operator = null, $second = null)
    {
        // массив sinleColumn
        if (is_array($first)) {
            $options = [];
            foreach ($first as $col => $val) {
                if (is_array($val)) {
                    $vals = array_values($val);
                    if (!is_numeric($col)) array_unshift($vals, $col);
                } else {
                    $vals = [$col, '=', $val];
                }
                // $this->addSingleWhereColumn($isOr, ...$vals);
                $options[] = static::singleColumn($isOr, ...$vals);
            }
            return $options;
        }

        // подготовка значения и оператора условия
        // [$second, $operator] = 
        static::prepareValueAndOperator(
            $second, $operator, func_num_args() === 3
        );
        $columns = [$first, $second];
        return new static('SingleColumn', compact('columns', 'operator', 'isOr'));
    }


    /**
     * WHERE IN.
     */
    public static function in(string $column, $values, bool $isOr = false, bool $isNot = false)
    {
        return (static::isQueryable($values))
        ? static::sub($isOr, $column, 'in', $values)
        : static::createStaticWithBindings('In', compact('column', 'values', 'isOr', 'isNot'));
    }


    // Where sub

    /**
     * Подзарос значения.
     */
    public static function sub(bool $isOr, string $column, $operator, $queryable = null)
    {
        // [$queryable, $operator] = 
        static::prepareValueAndOperator(
            $queryable, $operator, func_num_args() === 3
        );
        return new static('Sub', compact('column', 'operator', 'queryable', 'isOr'));
    }

    /**
     * Подзарос столбца.
     */
    public static function subColumn(bool $isOr, $queryable, $operator, $value = null)
    {
        // [$value, $operator] = 
        static::prepareValueAndOperator(
            $value, $operator, func_num_args() === 3
        );
        static::createStaticWithBindings(
            'SubColumn', compact('queryable', 'operator', 'value', 'isOr')
        );
    }


    public static function nested($queryable, bool $isOr = false)
    {
        return new static('Nested', compact('queryable', 'isOr'));
    }

    /**
     * Проверка наличия через подзарос.
     */
    public static function exists($queryable, bool $isOr = false, bool $isNot = false)
    {
        return new static('Exists', compact('queryable', 'isOr', 'isNot'));
    }


    /**
     * Where row values.
     */
    public static function rowValues(bool $isOr, array $columns, $operator, array $values = null)
    {
        // [$values, $operator] = 
        static::prepareValueAndOperator(
            $values, $operator, func_num_args() === 3
        );
        if (count($columns) !== count($values)) {
            throw new \InvalidArgumentException(
                'The number of columns must match the number of values'
            );
        }
        return static::createStaticWithBindings(
            'RowValues', compact('columns', 'operator', 'values', 'isOr')
        );
    }


    // Dates

    public static function dateBased(
        bool $isOr, string $function, string $column, string $operator, string $value = null
    ) {
        // [$value, $operator] = 
        static::prepareValueAndOperator(
            $value, $operator, func_num_args() === 4
        );
        return static::createStaticWithBindings(
            'DateBased', compact('column', 'operator', 'function', 'value', 'isOr')
        );
    }

    public static function dateBasedBetween(
        bool $isOr, string $function, string $column, array $values
    ) {
        $values = array_slice($values, 0, 2);
        return static::createStaticWithBindings(
            'BetweenDateBased', compact('column', 'function', 'values', 'isOr')
        );
    }


    // Json

    public static function jsonContains(
        string $column, $value, 
        bool $isOr = false, bool $isNot = false
    ) {
        return static::createStaticWithBindings(
            'JsonContains', compact('column', 'value', 'isOr', 'isNot')
        );
    }

    public static function jsonContainsPathAll(
        string $column, array $paths, bool $isOne = false, 
        bool $isOr = false, bool $isNot = false
    ) {
        return new static(
            'JsonContainsPath', compact('column', 'paths', 'isOne', 'isOr', 'isNot')
        );
    }

    public static function jsonLength(bool $isOr, string $column, $operator, $value = null)
    {
        return static::createStaticWithBindings(
            'JsonLength', compact('column', 'operator', 'value')
        );
    }
}
