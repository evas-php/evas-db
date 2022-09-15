<?php
/**
 * Трейт расширенных заросов базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Builders\InsertBuilder;
use Evas\Db\Builders\QueryBuilder;
use Evas\Db\Interfaces\QueryResultInterface;


trait DatabaseQueryTrait
{
    /**
     * Начало сборки sql-запроса на вставку.
     * @param string имя таблицы
     * @return InsertBuilderInterface
     */
    public function buildInsert(string $table): InsertBuilder
    {
        return new InsertBuilder($this, $table);
    }

    /**
     * Вставка записи или начало сборки sql-запроса на вставку.
     * @param string имя таблицы
     * @param array|object|null запись, если нужно вставить одну строку
     * @return InsertBuilderInterface|QueryResultInterface
     */
    public function insert(string $table, $row = null): object
    {
        $ib = $this->buildInsert($table);
        return empty($row) ? $ib : $ib->row($row)->query();
    }

    /**
     * Вставка нескольких записей.
     * @param string имя таблицы
     * @param array записи
     * @param array|null столбцы записей
     * @return QueryResultInterface
     */
    public function batchInsert(string $table, array $rows, array $columns = null)
    : QueryResultInterface
    {
        $ib = $this->buildInsert($table);
        if (!empty($columns)) $ib->columns($columns);
        return $ib->rows($rows)->query();
    }

    /**
     * Начало сборки sql-зароса select/update/delete через сборщик запроса.
     * @param string имя таблицы
     * @return QueryBuilder
     */
    public function buildQuery(string $table = null): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    /**
     * Начало сборки sql-запроса select через сборщик запроса.
     * @param string имя таблицы
     * @param array|string|null столбцы
     * @return QueryBuilder
     */
    public function select(string $table, $columns = null): QueryBuilder
    {
        if (!is_array($columns) && !is_null($columns)) {
            $columns =  func_get_args();
            $table = array_shift($columns);
        }
        return $this->buildQuery($table)->select($columns);
    }
}
