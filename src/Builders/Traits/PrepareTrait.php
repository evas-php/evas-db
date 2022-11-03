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
     * @param mixed значение (по ссылке)
     * @param mixed оператор (по ссылке)
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
        } else if (static::invalidValueAndOperator($value, $operator)) {
            throw new \InvalidArgumentException(json_encode([
                'error' => 'Illegal operator and value combination.',
                'operator' => $operator,
                'value' => $value,
            ]));
        }
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


    // ----------
    // For where & having
    // ----------

    /**
     * Итеративное выполение метода к столбцам.
     * @param string имя метода
     * @param array столбцы
     * @param bool|null использовать ли OR для склейки
     * @return self
     */
    protected function eachSingle(string $methodName, array $columns, bool $isOr = false)
    {
        foreach ($columns as $column => $value) {
            if (is_array($value)) {
                $args = array_values($value);
                if (!is_numeric($column)) array_unshift($args, $column);
            } else {
                $args = [$column, '=', $value];
            }
            $this->$methodName($isOr, ...$args);
        }
        return $this;
    }

    /**
     * Подготовка столбца со сборкой подзапроса.
     * @param mixed столбец
     * @param string|null тип экранируемых значений
     * @return string столбец
     */
    protected function prepareColumn($column, string $bindingsType = 'wheres')
    {
        // $column = trim($column, '()');
        [$column, $bindings] = $this->createSub($column);
        if (!empty($bindings)) $this->addBindings($bindingsType, $bindings);
        return $column;
    }
}
