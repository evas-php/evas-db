<?php
/**
 * Трейт сборки WHERE части с проверкой соответствия столбцов значениям.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Builders\Options\WhereOption;

trait WhereRowValuesTrait
{
    public function whereRowValues(array $columns, $operator, array $values = null)
    {
        $this->wheres[] = WhereOption::rowValues(false, ...func_get_args());
        return $this;
    }

    public function orWhereRowValues(array $columns, $operator, array $values = null)
    {
        $this->wheres[] = WhereOption::rowValues(true, ...func_get_args());
        return $this;
    }
}
