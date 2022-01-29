<?php
/**
 * Класс соединения с базой данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Base\BaseDatabase;
use Evas\Db\Builders\InsertBuilder;
use Evas\Db\Grammars\Grammar;
use Evas\Db\Grammars\MysqlGrammar;
use Evas\Db\Grammars\PgsqlGrammar;
use Evas\Db\Interfaces\QueryResultInterface;
use Evas\Db\Traits\DatabaseIdentityMapTrait;
use Evas\Db\Traits\DatabaseTableTrait;
use Evas\Db\Traits\DatabaseSchemaCacheTrait;

class Database extends BaseDatabase
{
    /**
     * Подключаем поддержку: 
     * таблиц, маппинга идентичности сущностей, кэша схемы базы данных.
     */
    use DatabaseTableTrait, DatabaseIdentityMapTrait;
    use DatabaseSchemaCacheTrait;

    /** @static array маппинг расширенных грамматик СУБД */
    public static $grammarByDrivers = [
        'mysql' => MysqlGrammar::class,
        'pgsql' => PgsqlGrammar::class,
    ];

    /** @var Grammar грамматика СУБД для соединения */
    protected $grammar;


    /**
     * Получение грамматики СУБД для соединения.
     * @return Grammar
     */
    public function grammar(): Grammar
    {
        if (!$this->grammar) {
            $grammar = static::$grammarByDrivers[$this->driver] ?? Grammar::class;
            $this->grammar = new $grammar($this);
        }
        return $this->grammar;
    }

    /**
     * Начало сборки INSERT-запроса.
     * @param string имя таблицы
     * @param array|object|null значения записи для сохранения с автосборкой
     * @return InsertBuilder|QueryResultInterface
     */
    public function insert(string $tbl, $row = null): object
    {
        $ib = new InsertBuilder($this, $tbl);
        return empty($row) ? $ib : $ib->row($row)->query();
    }

    /**
     * Вставка нескольких записей.
     * @param string имя таблицы
     * @param array значения записей
     * @param array|null столбцы записи
     * @return QueryResultInterface
     */
    public function batchInsert(string $tbl, array $rows, array $columns = null): QueryResultInterface
    {
        $ib = $this->insert($tbl);
        if (!empty($columns)) $ib->keys($columns);
        return $ib->rows($rows)->query();
    }
}
