<?php
/**
 * Трейт добавления having.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait HavingTrait
{
    // ----------
    // And/Or Having Raw
    // ----------

    /**
     * Добавление having sql-строкой.
     * @param array sql-строка
     * @param array|null значения для экранирования
     * @param bool|null использовать ли OR для склейки
     * @return self
     */
    public function havingRaw(string $sql, array $values = [], bool $isOr = false)
    {
        return $this->pushHaving('Raw', compact('sql', 'values', 'isOr'));
    }

    /**
     * Добавление having sql-строкой через OR.
     * @param array sql-строка
     * @param array|null значения для экранирования
     * @return self
     */
    public function orHavingRaw(string $sql, array $values = [])
    {
        return $this->havingRaw($sql, $values);
    }

    // ----------
    // And/Or Having
    // ----------

    /**
     * Вспомогательная функция для добавления having сравнения.
     * @param bool использовать ли OR для склейки
     * @param string столбец
     * @param mixed оператор или значение
     * @param mixed|null значение или null
     * @return self
     */
    protected function addHaving(bool $isOr, string $column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 3
        );
        return is_null($value)
        ? $this->havingNull($column, $isOr)
        : $this->pushHaving('Single', compact('column', 'operator', 'value', 'isOr'));
    }

    /**
     * Добавление having сравнения значения столбца через AND.
     * @param string столбец
     * @param mixed оператор или значение
     * @param mixed|null значение или null
     * @return self
     */
    public function having(string $column, $operator, $value = null)
    {
        return $this->addHaving(false, ...func_get_args());
    }

    /**
     * Добавление having сравнения значения столбца через OR.
     * @param string столбец
     * @param mixed оператор или значение
     * @param mixed|null значение или null
     * @return self
     */
    public function orHaving(string $column, $operator, $value = null)
    {
        return $this->addHaving(true, ...func_get_args());
    }

    // ----------
    // And/Or Having Is (Not) Null
    // ----------

    /**
     * Добавление having is null.
     * @param string столбец
     * @param bool использовать ли OR для склейки having
     * @param bool использовать ли NOT перед null
     * @return self
     */
    public function havingNull(string $column, bool $isOr = false, bool $isNot = false)
    {
        return $this->pushHaving('Null', compact('column', 'isOr', 'isNot'));
    }

    /**
     * Добавление OR having is null.
     * @param string столбец
     * @return self
     */
    public function orHavingNull(string $column)
    {
        return $this->havingNull($column, true);
    }

    /**
     * Добавление and having is NOT null.
     * @param string столбец
     * @return self
     */
    public function havingNotNull(string $column)
    {
        return $this->havingNull($column, false, true);
    }

    /**
     * Добавление OR having is NOT null.
     * @param string столбец
     * @return self
     */
    public function orHavingNotNull(string $column)
    {
        return $this->havingNull($column, true, true);
    }
}
