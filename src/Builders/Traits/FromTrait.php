<?php
/**
 * Трейт сборки FROM части.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait FromTrait
{
    /** @var array FROM часть */
    public $from = [];

    /**
     * Сбросить from.
     * @return self
     */
    public function resetFrom()
    {
        $this->from = [];
        $this->bindings['from'] = [];
        return $this;
    }

    /**
     * Установка from sql сторокой.
     * @param string sql строка
     * @param array|null экранируемые значения
     * @param string|null псевдоним
     * @return self
     */
    public function fromRaw(string $sql, array $bindings = [], string $as = null)
    {
        $sql = $this->wrapRoundBrackets($sql);
        $this->from[] = is_null($as) ? $sql : ("$sql AS " . $this->wrap($as));
        return $this->addBindings('from', $bindings);
    }

    /**
     * Установка from таблицей или sql-подзапросом.
     * @param string|\Closure|self подзапрос
     * @param string|null псевдоним
     * @return self
     */
    public function from($table, string $as = null)
    {
        if (is_array($table)) {
            foreach ($table as $_as => $_table) {
                $this->from($_table, is_string($_as) ? $_as : null);
            }
            return $this;
        }
        if (static::isQueryable($table) && !is_null($as)) {
            return $this->fromSub($table, $as);
        }
        $table = $this->wrap($table);
        $this->from[] = is_null($as) ? $table : ($table . ' AS ' . $this->wrap($as));
        return $this;
    }

    /**
     * Установка from sql-подзапросом.
     * @param \Closure|self|string
     * @param string псевдоним
     * @return self
     */
    public function fromSub($query, string $as)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->fromRaw($sql, $bindings, $as);
    }
}
