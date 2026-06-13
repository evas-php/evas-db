<?php
/**
 * Трейт сборки WHERE части с проверкой соответствия столбцов значениям или подзапросу.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereRowTrait
{
    /**
     * Реальное добавление where с проверкой соответствия столбцов значениям.
     * @param bool использовать ли OR для склейки
     * @param array столбцы
     * @param string|\Closure|self|array оператор или подзарос или значения
     * @param string|\Closure|self|array|null подзарос или значения или null
     * @return self
     * @throws \InvalidArgumentException
     */
    protected function pushWhereRow(
        bool $isOr, array $columns, $operator, $values = null
    ) {
        $count = count($columns);
        $this->prepareValueAndOperator($values, $operator, func_num_args() === 3);
        if (is_array($values)) {
            if ($count < 2) {
                return $this->pushSingleWhere($isOr, $columns[0], $operator, $values);
            }
            if ($count !== count($values)) {
                throw new \InvalidArgumentException('The number of columns must match the number of values');
            }
            [$sql, $bindings] = [null, $values];
        } else {
            if ($count < 2) {
                return $this->pushWhereSub($isOr, $columns[0], $operator, $values);
            }
            [$sql, $bindings] = $this->createSub($values);
        }
        return $this->pushWhere('Row', compact('columns', 'operator', 'sql', 'bindings', 'isOr'));
    }

    /**
     * Добавление where с проверкой соответствия столбцов значениям через AND.
     * @param array столбцы
     * @param string|\Closure|self|array оператор или подзарос или значения
     * @param string|\Closure|self|array|null подзарос или значения или null
     * @return self
     */
    public function whereRow(array $columns, $operator, $values = null)
    {
        return $this->pushWhereRow(false, ...func_get_args());
    }

    /**
     * Добавление where с проверкой соответствия столбцов значениям через OR.
     * @param array столбцы
     * @param string|\Closure|self|array оператор или подзарос или значения
     * @param string|\Closure|self|array|null подзарос или значения или null
     * @return self
     */
    public function orWhereRow(array $columns, $operator, $values = null)
    {
        return $this->pushWhereRow(true, ...func_get_args());
    }
}
