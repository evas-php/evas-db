<?php
/**
 * Базовый класс соединения с базой данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Interfaces\BaseDatabaseInterface;
use Evas\Db\Traits\DatabaseBaseQueryTrait;
use Evas\Db\Traits\DatabaseConnectionTrait;
use Evas\Db\Traits\DatabaseGrammarTrait;
use Evas\Db\Traits\DatabaseQuoteQueryPropsTrait;
use Evas\Db\Traits\DatabaseTransactionsTrait;

class BaseDatabase implements BaseDatabaseInterface
{
    // базовые запросы БД
    use DatabaseBaseQueryTrait;
    // соединение БД
    use DatabaseConnectionTrait;
    // грамматика БД
    use DatabaseGrammarTrait;
    // экранирование параметров запроса
    use DatabaseQuoteQueryPropsTrait;
    // транзакции
    use DatabaseTransactionsTrait;

    /**
     * Конструктор.
     * @param array|null параметры соединения
     */
    public function __construct(array $props = null)
    {
        if ($props) foreach ($props as $name => $value) {
            $this->$name = $value;
        }
        $this->driver = strtolower($this->driver);
    }

    // 

    /**
     * Получение id последней вставленной записи.
     * @param string|null имя таблицы
     * @return int
     */
    public function lastInsertId(string $table = null): int
    {
        return intval($this->getPdo()->lastInsertId($tbl));
    }


    // Работа с ошибками

    /**
     * Получить расширенную информацию об ошибке последнего запроса.
     * @return array|null
     */
    public function errorInfo(): ?array
    {
        return $this->isOpen() ? $this->getPdo()->errorInfo() : null;
    }

    /**
     * Дебаг запроса.
     * @param string sql
     * @param array|null параметры запроса
     */
    public function debugSql(string $sql, array $props = null)
    {
        evasDebug([
            "query to `$this->host`:`$this->dbname`" => compact('sql', 'props')
        ]);
    }
}
