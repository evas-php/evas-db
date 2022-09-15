<?php
/**
 * Трейт базовых заросов базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Exceptions\DatabaseQueryException;
use Evas\Db\Interfaces\QueryResultInterface;
use Evas\Db\QueryResult;

trait DatabaseBaseQueryTrait
{
    /** @var \PDOStatement statement последнего запроса */
    protected $lastStmt;

    /**
     * Получение объекта результата запроса.
     * @param \PDOStatement statement запроса
     * @return QueryResultInterface
     */
    protected function getQueryResult(\PDOStatement &$stmt): QueryResultInterface
    {
        $this->lastStmt = &$stmt;
        return new QueryResult($stmt, $this);
    }

    /**
     * Закрытие курсора statement последнего запроса.
     * @return self
     */
    protected function closeCursor()
    {
        if (!empty($this->lastStmt)) $this->lastStmt->closeCursor();
        return $this;
    }

    /**
     * Получение подготовленного запроса.
     * @param string sql-запрос
     * @return \PDOStatement подготовленный запрос
     * @throws DatabaseQueryException
     */
    public function prepare(string $sql): \PDOStatement
    {
        try {
            $this->closeCursor();
            return $this->getPdo()->prepare($sql);
        } catch (\PDOException $e) {
            throw DatabaseQueryException::fromErrorInfo($this->errorInfo(), $sql);
        }
    }

    /**
     * Выполнение подготовленного запроса.
     * @param \PDOStatement подготовленный запрос
     * @param array|null экранируемые параметры запроса
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function execute(\PDOStatement $stmt, array $props = null): QueryResultInterface
    {
        try {
            $this->debugSql($stmt->queryString, $props);
            if (false === $stmt->execute($props)) {
                throw DatabaseQueryException::fromStmt($stmt, $props);
            }
        } catch (\PDOException $e) {
            throw DatabaseQueryException::fromStmt($stmt, $props);
        }
        return $this->getQueryResult($stmt);
    }

    /**
     * Выполнение запроса с возвращением количества затронутых строк.
     * @param string sql-запрос
     * @return int кол-во затронутых строк
     * @throws DatabaseQueryException
     */
    public function pdoExec(string $sql): int
    {
        try {
            $this->closeCursor();
            $rowCount = $this->getPdo()->exec($sql);
        } catch (\PDOException $e) {
            throw DatabaseQueryException::fromErrorInfo($this->errorInfo(), $sql); 
        }
        if (false === $rowCount) {
            throw DatabaseQueryException::fromErrorInfo($this->errorInfo(), $sql); 
        }
        return $rowCount;
    }

    /**
     * Выполнение запроса без подготовки.
     * @param string sql-запрос
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function pdoQuery(string $sql): QueryResultInterface
    {
        $this->debugSql($sql);
        try {
            $this->closeCursor();
            // перехватываем переключение базы данных для смены атрибута
            if (strpos($sql, 'USE') === 0 && in_array($sql[3] ?? '', [' ', '`'])) {
                $this->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
                $stmt = $this->getPdo()->query($sql);
                $this->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            } else {
                $stmt = $this->getPdo()->query($sql);
            }
        } catch (\PDOException $e) {
            throw DatabaseQueryException::fromErrorInfo($this->errorInfo(), $sql);
        }
        return $this->getQueryResult($stmt);
    }

    /**
     * Выполнение запроса с автоподготовкой.
     * @param string sql-запрос
     * @param array|null экранируемые параметры запроса
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function query(string $sql, array $props = null): QueryResultInterface
    {
        return empty($props) 
        ? $this->pdoQuery($sql)
        : $this->execute($this->prepare($sql), $props);
    }

    /**
     * Выполнение нескольких запросов.
     * @param array sql-запросы
     * @param array|null экранируемые параметры запроса
     * @return array of QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function batchQuery(array $sqls, array $props = null): array
    {
        $result = [];
        $this->beginTransaction();
        foreach ($sqls as &$sql) {
            $result[] = $this->query($sql, $props);
        }
        $this->commit();
        return $result;
    }

    /**
     * Выполнение нескольких запросов переданных строкой.
     * @param string sql-запросы разделённые символом ;
     * @param array|null экранируемые параметры запроса
     * @return array of QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function stringBatchQuery(string $batchSql, array $props = null): array
    {
        $sqls = explode(';', trim($batchSql));
        if (empty($sqls[count($sqls) - 1])) array_pop($sqls);
        return empty($sqls) ? [] : $this->batchQuery($sqls, $props);
    }
}
