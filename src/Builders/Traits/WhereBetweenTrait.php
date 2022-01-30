<?php
/**
 * Трейт добавления where between.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereBetweenTrait
{
    // ----------
    // Or/And Where Between/Not Between
    // ----------

    /**
     * Добавление where between.
     * По умолчанию склейка через AND.
     * @param string столбец
     * @param array значения [min, max]
     * @param bool использовать ли OR для склейки where
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function whereBetween(string $column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $values = array_slice($values, 0, 2);
        return $this->pushWhere('Between', compact('column', 'values', 'isOr', 'isNot'));
    }

    /**
     * Добавление where OR between.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereBetween(string $column, array $values)
    {
        return $this->between($column, $values, true);
    }

    /**
     * Добавление where AND NOT between.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function whereNotBetween(string $column, array $values)
    {
        return $this->between($column, $values, false, true);
    }

    /**
     * Добавление where OR NOT between.
     * @param string столбец
     * @param array значения [min, max]
     * @return self
     */
    public function orWhereNotBetween(string $column, array $values)
    {
        return $this->between($column, $values, true, true);
    }

    // ----------
    // Or/And Where Between/Not Between Columns
    // ----------

    /**
     * Добавление where between со значениями столбцов в качестве значений.
     * По умолчанию склейка через AND.
     * @param string столбец
     * @param array столбцы из которых достать значение [min, max]
     * @param bool использовать ли OR для склейки where
     * @param bool использовать ли NOT перед between
     * @return self
     */
    public function whereBetweenColumns(string $column, array $values, bool $isOr = false, bool $isNot = false)
    {
        $columns = array_slice($values, 0, 2);
        return $this->pushWhere('BetweenColumns', compact('column', 'columns', 'isOr', 'isNot'));
    }

    /**
     * Добавление where OR between со значениями столбцов в качестве значений.
     * @param string столбец
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function orWhereBetweenColumns(string $column, array $values)
    {
        return $this->between($column, $values, true);
    }

    /**
     * Добавление where AND NOT between со значениями столбцов в качестве значений.
     * @param string столбец
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function whereNotBetweenColumns(string $column, array $values)
    {
        return $this->between($column, $values, false, true);
    }

    /**
     * Добавление where OR NOT between со значениями столбцов в качестве значений.
     * @param string столбец
     * @param array столбцы из которых достать значение [min, max]
     * @return self
     */
    public function orWhereNotBetweenColumns(string $column, array $values)
    {
        return $this->between($column, $values, true, true);
    }
}
