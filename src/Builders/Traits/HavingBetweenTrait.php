<?php
/**
 * Трейт сборки having between.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait HavingBetweenTrait
{
    // ----------
    // Or/And Having (Not) Between
    // ----------

    /**
     * Установка having between.
     * По умолчанию склейка через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @param bool использовать ли OR для склейки having
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function havingBetween(string $column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $values = array_slice($values, 0, 2);
        return $this->pushHaving('Between', compact('column', 'values', 'isOr', 'isNot'));
    }

    /**
     * Установка having OR between.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orHavingBetween(string $column, array $values)
    {
        return $this->havingBetween($column, $values, true);
    }

    /**
     * Установка having AND NOT between.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function havingNotBetween(string $column, array $values)
    {
        return $this->havingBetween($column, $values, false, true);
    }

    /**
     * Установка having OR NOT between.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orHavingNotBetween(string $column, array $values)
    {
        return $this->havingBetween($column, $values, true, true);
    }

    // ----------
    // Or/And Where (Not) Between Columns
    // ----------

    /**
     * Установка having between со значениями столбцов в качестве значений.
     * По умолчанию склейка через AND.
     * @param string столбец
     * @param array столбцы из которых достать значение [min, max]
     * @param bool использовать ли OR для склейки having
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function havingBetweenColumns(
        string $column, array $columns, bool $isOr = false, bool $isNot = false
    ) {
        $values = array_slice($columns, 0, 2);
        return $this->pushHaving('BetweenColumns', compact('column', 'values', 'isOr', 'isNot'));
    }

    /**
     * Установка having OR between со значениями столбцов в качестве значений.
     * @param string столбец
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function orHavingBetweenColumns(string $column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, true);
    }

    /**
     * Установка having AND NOT between со значениями столбцов в качестве значений.
     * @param string столбец
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function havingNotBetweenColumns(string $column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, false, true);
    }

    /**
     * Установка having OR NOT between со значениями столбцов в качестве значений.
     * @param string столбец
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function orHavingNotBetweenColumns(string $column, array $columns)
    {
        return $this->havingBetweenColumns($column, $columns, true, true);
    }
}
