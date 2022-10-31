<?php
/**
 * Трейт сборки HAVING части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\HavingOption;

trait HavingTrait
{
    /** @var array HAVING часть */
    public $havings = [];


    // Help methods

    /**
     * Добавление having условия в сборку.
     * @param string тип having
     * @param array параметры условия
     * @return self
     */
    protected function pushHaving(string $type, array $having)
    {
        $having['type'] = $type;
        $this->havings[] = $having;
        if (!empty($having['bindings'])) {
            $this->addBindings('havings', $having['bindings']);
        }
        return $this;
    }

    protected function pushSingleHaving(bool $isOr, $column, $operator = null, $value = null)
    {
        // массив where условий
        if (is_array($column)) {
            return $this->eachSingle('pushSingleHaving', $column, $isOr);
        }

        // подготовка значения и оператора условия
        static::prepareValueAndOperator($value, $operator, func_num_args() === 3);

        // IS NULL условие
        if (is_null($value)) return $this->havingNull($column, $isOr, $operator !== '=');

        // single условие
        $column = $this->prepareColumn($column, 'havings');
        $bindings = [$value];
        return $this->pushHaving('Single', compact('column', 'operator', 'bindings', 'isOr'));
    }

    /**
     * Вспомогательная функция для добавление having агрегации.
     * @param bool использовать ли OR для склейки
     * @param string функция агрегации
     * @param string столбец агрегации
     * @param mixed оператор или значение
     * @param mixed|null значение или null
     * @return self
     */
    protected function pushHavingAggregate(
        bool $isOr, string $function, string $column, $operator, $value = null
    ) {
        static::prepareValueAndOperator($value, $operator, func_num_args() === 4);
        $column = $this->getAggregateColumn($function, $column);
        return $this->pushSingleHaving($isOr, $column, $operator, $value);
    }


    // ----------
    // Or/And Having Raw
    // ----------


    public function havingRaw(string $sql, array $bindings = [], bool $isOr = false)
    {
        return $this->pushHaving('Raw', compact('sql', 'bindings', 'isOr'));
    }

    public function orHavingRaw(string $sql, array $bindings = [])
    {
        return $this->havingRaw($sql, $bindings);
    }


    // ----------
    // Or/And Having
    // ----------


    public function having($column, $operator = null, $value = null)
    {
        return $this->pushSingleHaving(false, ...func_get_args());
    }

    public function orHaving($column, $operator = null, $value = null)
    {
        return $this->pushSingleHaving(true, ...func_get_args());
    }


    // ----------
    // Or/And Having Is (Not) Null
    // ----------

    public function havingNull($column, bool $isOr = false, bool $isNot = false)
    {
        if (is_array($column)) {
            foreach ($column as $sub) {
                $this->havingNull($sub, $isOr, $isNot);
            }
            return $this;
        }
        $column = $this->prepareColumn($column, 'havings');
        return $this->pushHaving('Null', compact('column', 'isOr', 'isNot'));
    }

    public function orHavingNull($column)
    {
        return $this->havingNull($column, true, false);
    }

    public function havingNotNull($column)
    {
        return $this->havingNull($column, false, true);
    }

    public function orHavingNotNull($column)
    {
        return $this->havingNull($column, true, true);
    }


    // ----------
    // Or/And Having (Not) In
    // ----------

    /**
     * Добавление having AND/OR (NOT) IN.
     * @param string|\Closure|self столбец или подзарос столбца
     * @param array|\Closure|self массив значений или подзапрос
     * @param bool|null использовать ли OR для склейки
     * @param bool|null использовать ли NOT
     * @return self
     */
    public function havingIn($column, $values, bool $isOr = false, bool $isNot = false)
    {
        $column = $this->prepareColumn($column);
        if (is_array($values)) {
            $bindings = $values;
        } else {
            [$values, $bindings] = $this->createSub($values);
        }
        return $this->pushHaving('In', compact('column', 'values', 'bindings', 'isOr', 'isNot'));
    }

    /**
     * Добавление having OR IN.
     * @param string|\Closure|self столбец или подзарос столбца
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function orHavingIn($column, $values)
    {
        return $this->whereIn($column, $values, true);
    }

    /**
     * Добавление having AND NOT IN.
     * @param string|\Closure|self столбец или подзарос столбца
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function havingNotIn($column, $values)
    {
        return $this->whereIn($column, $values,false, true);
    }

    /**
     * Добавление having OR NOT IN.
     * @param string|\Closure|self столбец или подзарос столбца
     * @param array|\Closure|self массив значений или подзапрос
     * @return self
     */
    public function orHavingNotIn($column, $values)
    {
        return $this->whereIn($column, $values, true, true);
    }


    // Aggregates

    public function havingAggregate(string $function, string $column, $operator, $value = null)
    {
        return $this->pushHavingAggregate(false, ...func_get_args());
    }

    public function orHavingAggregate(string $function, string $column, $operator, $value = null)
    {
        return $this->pushHavingAggregate(true, ...func_get_args());
    }
}
