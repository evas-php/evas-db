<?php
/**
 * Интерфейс сборщика заросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryResultInterface;
use Evas\Db\Interfaces\BaseQueryBuilderInterface;

interface QueryBuilderInterface extends BaseQueryBuilderInterface
{
    /**
     * Установка from sql сторокой.
     * @param string sql строка
     * @param array|null экранируемые значения
     * @return self
     */
    public function fromRaw(string $sql, array $bindings = []);

    /**
     * Установка from таблицей или sql-подзапросом.
     * @param string|\Closure|self таблица или подзапрос
     * @param string|null псевдоним
     * @return self
     */
    public function from($table, string $as = null);

    /**
     * Установка from sql-подзапросом.
     * @param \Closure|self|string
     * @param string псевдоним
     * @return self
     */
    public function fromSub($query, string $as);
}
