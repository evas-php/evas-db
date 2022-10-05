<?php
/**
 * Трейт грамматики сброрки where/having части запроса СУБД.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars\Traits;

trait GrammarQueryWhereTrait
{
    /**
     * Получение разделителя между wheres/havings.
     * @param array where
     * @return string
     */
    protected function getWhereSeparator(array $where): string
    {
        return ($where['isOr'] ?? null) ? ' OR ' : ' AND ';
    }

    /**
     * Получение NOT для условия по необходимости.
     * @param array where
     * @return string
     */
    protected function getNot(array $where): string
    {
        return ($where['isNot'] ?? null) ? 'NOT ' : '';
    }

    /**
     * Сборка одиночного условия.
     * @param array where
     * @return string
     */
    protected function buildWhereSingle(array $where)
    {
        return "{$this->wrap($where['column'])} {$where['operator']} ?";
    }

    /**
     * Сборка одиночного условия стобцов.
     * @param array where
     * @return string
     */
    protected function buildWhereSingleColumn(array $where)
    {
        return "{$this->wrap($where['first'])} {$where['operator']} {$this->wrap($where['second'])}";
    }

    /**
     * Сборка вложенного условия.
     * @param array where
     * @return string
     */
    protected function buildWhereNested(array $where)
    {
        return "({$where['sql']})";
    }

    /**
     * Сборка одиночного условия с подзапросом значения.
     * @param array where
     * @return string
     */
    protected function buildWhereSub(array $where)
    {
        return "{$this->wrap($where['column'])} {$where['operator']} ({$where['sql']})";
    }

    /**
     * Сборка sql-условия как есть.
     * @param array where
     * @return string
     */
    protected function buildWhereRaw(array $where)
    {
        return $where['sql'];
    }

    /**
     * Сборка одиночного условия с проверкой на NULL / NOT NULL.
     * @param array where
     * @return string
     */
    protected function buildWhereNull(array $where)
    {
        return "{$this->wrap($where['column'])} IS {$this->getNot($where)}NULL";
    }

    /**
     * Сборка одиночного условия IN.
     * @param array where
     * @return string
     */
    protected function buildWhereIn(array $where)
    {
        $quotes = implode(', ', array_fill(0, count($where['values']), '?'));
        return "{$this->wrap($where['column'])} {$this->getNot($where)}IN({$quotes})";
    }

    /**
     * Сборка условия EXISTS.
     * @param array where
     * @return string
     */
    protected function buildWhereExists(array $where)
    {
        return "{$this->getNot($where)}EXISTS ({$where['sql']})";
    }

    /**
     * Сборка условия BETWEEN значения.
     * @param array where
     * @return string
     */
    protected function buildWhereBetween(array $where)
    {
        return "{$this->wrap($where['column'])} {$this->getNot($where)}BETWEEN ? AND ?";
    }

    /**
     * Сборка условия BETWEEN столбцы.
     * @param array where
     * @return string
     */
    protected function buildWhereBetweenColumns(array $where)
    {
        $not = $this->getNot($where);
        $min = $this->wrap($where['columns'][0]);
        $max = $this->wrap($where['columns'][1]);
        return "{$this->wrap($where['column'])} {$not}BETWEEN {$min} AND {$max}";
    }

    /**
     * Сборка условия базирующегося на дате и времени.
     * @param array where
     * @return string
     */
    protected function buildWhereDateBased(array $where)
    {
        $not = $this->getNot($where);
        return "{$where['date_operator']}({$this->wrap($where['column'])}) {$where['operator']} ?";
    }

    /**
     * Сборка условия BETWEEN базирующегося на дате и времени.
     * @param array where
     * @return string
     */
    protected function buildWhereBetweenDateBased(array $where)
    {
        $not = $this->getNot($where);
        return "{$where['date_operator']}({$this->wrap($where['column'])}) {$not}BETWEEN ? AND ?";
    }

    /**
     * Сборка множественных условий.
     * @param array where
     * @return string
     */
    protected function buildWhereRowValues(array $where)
    {
        $sql = '';
        foreach ($where['columns'] as $i => $column) {
            if ($i > 0) $sql .= $this->getWhereSeparator($where);
            $sql .= "{$this->wrap($column)} {$where['operator']} ?";
        }
        return "($sql)";
    }
}
