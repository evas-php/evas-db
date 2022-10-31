<?php
/**
 * @todo Трейт сборки WHERE части json.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereJsonTrait
{
    public function whereJsonContains(
        string $column, $value, bool $isOr = false, bool $isNot = false
    ) {
        return $this->pushWhere('JsonContains', compact('column', 'value', 'isOr', 'isNot'));
    }

    public function orWhereJsonContains(string $column, $value)
    {
        return $this->whereJsonContains($column, $value, true);
    }

    public function whereNotJsonContains(string $column, $value)
    {
        return $this->whereJsonContains($column, $value, false, true);
    }

    public function orWhereNotJsonContains(string $column, $value)
    {
        return $this->whereJsonContains($column, $value, true, true);
    }



    public function whereJsonContainsPathAll(
        string $column, array $paths, bool $isOne = false, bool $isOr = false, bool $isNot = false
    ) {
        return $this->pushWhere(
            'JsonContainsPath', compact('column', 'paths', 'isOne', 'isOr', 'isNot')
        );
    }

    public function orWhereJsonContainsPathAll(string $column, $value, bool $isOne = false)
    {
        return $this->whereJsonContainsPathAll($column, $paths, false, true);
    }

    public function whereNotJsonContainsPathAll(string $column, $value, bool $isOne = false)
    {
        return $this->whereJsonContainsPathAll($column, $paths, true);
    }

    public function orWhereNotJsonContainsPathAll(string $column, $value, bool $isOne = false)
    {
        return $this->whereJsonContainsPathAll($column, $paths, true, true);
    }



    protected function pushWhereJsonLength(bool $isOr, string $column, $operator, $value = null)
    {
        $this->prepareValueAndOperator($value, $operator, func_num_args() === 3);
        return $this->pushWhere('JsonLength', compact('column', 'operator', 'value'));
    }

    public function whereJsonLength(string $column, $operator, $value = null)
    {
        return $this->pushWhereJsonLength(false, ...func_get_args());
    }

    public function orWhereJsonLength(string $column, $operator, $value = null)
    {
        return $this->pushWhereJsonLength(true, ...func_get_args());
    }
}
