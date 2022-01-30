<?php
/**
 * Трейт сборки where json.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait WhereJsonTrait
{
    /**
     * @todo Доделать, протестировать!
     */

    
    public function whereJsonContains(string $column, $value, bool $isOr = false, bool $isNot = false)
    {
        return $this->pushWhere('JsonContains', compact('column', 'value', 'isOr', 'isNot'));
    }

    public function orWhereJsonContains(string $column, $value)
    {
        return $this->whereJsonContains($column, $value, true);
    }

    public function whereJsonDoesntContain(string $column, $value)
    {
        return $this->whereJsonContains($column, $value, false, true);
    }

    public function orWhereJsonDoesntContain(string $column, $value)
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

    public function whereJsonContainsPathOne(string $column, array $paths)
    {
        return $this->whereJsonContainsPathAll($column, $paths, true);
    }

    public function orWhereJsonContainsPathAll(string $column, array $paths)
    {
        return $this->whereJsonContainsPathAll($column, $paths, false, true);
    }

    public function orWhereJsonContainsPathOne(string $column, array $paths)
    {
        return $this->whereJsonContainsPathAll($column, $paths, true, true);
    }

    protected function addWhereJsonLength(bool $isOr, string $column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 3
        );
        return $this->pushWhere('JsonLength', compact('column', 'operator', 'value'));
    }

    public function whereJsonLength(string $column, $operator, $value = null)
    {
        return $this->addWhereJsonLength(false, $column, $operator, $value);
    }

    public function orWhereJsonLength(string $column, $operator, $value = null)
    {
        return $this->addWhereJsonLength(true, $column, $operator, $value);
    }
}
