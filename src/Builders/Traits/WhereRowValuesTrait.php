<?php
/**
 * Трейт сборки where с проверкой соответствия столбцов значениям.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereRowValuesTrait
{
    /**
     * Добавление where с проверкой соответствия столбцов значениям через AND.
     * @param array столбцы
     * @param string оператор
     * @param array значения
     * @param bool|null использовать ли OR для склейки
     * @return self
     */
    public function whereRowValues(array $columns, string $operator, array $values, bool $isOr = false)
    {
        if (count($columns) !== count($values)) {
            throw new \InvalidArgumentException('The number of columns must match the number of values');
        }
        return $this->pushWhere('RowValues', compact('columns', 'operator', 'values', 'isOr'));
    }

    /**
     * Добавление where с проверкой соответствия столбцов значениям через OR.
     * @param array столбцы
     * @param string оператор
     * @param array значения
     * @return self
     */
    public function orWhereRowValues(array $columns, string $operator, array $values)
    {
        return $this->whereRowValues($columns, $operator, $values, true);
    }
}
