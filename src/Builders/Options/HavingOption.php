<?php
namespace Evas\Db\Builders\Options;

use Evas\Db\Builders\Options\AbstractWhereOrHavingOption;

class HavingOption extends AbstractWhereOrHavingOption
{
    public $to = 'havings';

    public static function single(bool $isOr, $column, $operator, $value = null)
    {
        // [$value, $operator] = 
        static::prepareValueAndOperator(
            $value, $operator, func_num_args() === 3
        );
        return is_null($value)
        ? static::null($column, $isOr)
        : static::createStaticWithBindings(
            'Single', compact('column', 'operator', 'value', 'isOr')
        );
    }

    public static function aggregate(
        bool $isOr, string $function, string $column, $operator, $value = null
    ) {
        // [$value, $operator] = 
        static::prepareValueAndOperator(
            $value, $operator, func_num_args() === 4
        );
        return static::createStaticWithBindings(
            'Single', compact('function', 'column', 'operator', 'value', 'isOr');
        );
    }
}
