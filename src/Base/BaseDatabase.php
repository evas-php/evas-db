<?php
/**
 * Базовый класс соединения с базой данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Base;

use \PDO;
use \PDOException;
use \PDOStatement;
use \UnexpectedValueException;
use Evas\Db\Exceptions\DatabaseConnectionException;
use Evas\Db\Exceptions\DatabaseQueryException;
use Evas\Db\Base\QueryResult;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Interfaces\QueryResultInterface;

/**
 * Константы для класса по умолчанию.
 */
if (!defined('EVAS_DB_OPTIONS')) {
    define('EVAS_DB_OPTIONS', [
        PDO::ATTR_EMULATE_PREPARES => false, // помогает с приведением типов из базы в php
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        // PDO::ATTR_CASE => PDO::CASE_LOWER,
        // PDO::ATTR_AUTOCOMMIT => false,
        // PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}
if (!defined('EVAS_DB_DRIVER')) define('EVAS_DB_DRIVER', 'mysql');
if (!defined('EVAS_DB_HOST')) define('EVAS_DB_HOST', 'localhost');
if (!defined('EVAS_DB_CHARSET')) define('EVAS_DB_CHARSET', 'utf8');


if (!defined('EVAS_DB_QUERY_RESULT_CLASS')) {
    define('EVAS_DB_QUERY_RESULT_CLASS', QueryResult::class);
}

class BaseDatabase implements DatabaseInterface
{
    /** @static array доступные функции экранирования объектов */
    const QUOTE_OBJECTS_FUNCS = [
        'null' => '\'NULL\'; intval', 
        'json' => '\json_encode',
        'serialize' => '\serialize', 
    ];

    /** @var string драйвер */
    public $driver = EVAS_DB_DRIVER;
    /** @var string хост */
    public $host = EVAS_DB_HOST;
    /** @var string имя базы данных */
    public $dbname;

    /** @var string имя пользователя */
    public $username;
    /** @var string пароль пользователя */
    public $password;

    /** @var array опции соединения */
    public $options = EVAS_DB_OPTIONS;
    /** @var string кодировка */
    public $charset = EVAS_DB_CHARSET;

    /** @var string класс ответов */
    public $queryResultClass = EVAS_DB_QUERY_RESULT_CLASS;

    /** @var pdo */
    protected $pdo;

    /** @var pdo read pdo */
    protected $readPdo;

    /** @var PDOStatement последний statement запроса */
    protected $lastStmt;

    /** @var string имя функции для экранирования объектов и массивов */
    protected $quoteObjectsFunc = self::QUOTE_OBJECTS_FUNCS['null'];


    /**
     * Конструктор.
     * @param array|null параметры
     */
    public function __construct(array $params = null)
    {
        if ($params) foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }

    // Работа с соединением

    /**
     * Открытие соединения.
     * @return self
     * @throws DatabaseConnectionException
     */
    public function open(): DatabaseInterface
    {
        $dsn = "$this->driver:host=$this->host";
        if (!empty($this->dbname)) $dsn .= ";dbname=$this->dbname";
        if (!empty($this->charset)) $dsn .= ";charset=$this->charset";
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage());
        }
        return $this;
    }

    /**
     * Закрытие соединения.
     * @return self
     */
    public function close(): DatabaseInterface
    {
        $this->pdo = null;
        return $this;
    }

    /**
     * Проверка открытости соединения.
     * @return bool
     */
    public function isOpen(): bool
    {
        return null !== $this->pdo ? true : false;
    }

    /**
     * Получение PDO.
     * @return \PDO
     * @throws DatabaseConnectionException
     */
    public function getPdo(): PDO
    {
        if (! $this->isOpen()) $this->open();
        return $this->pdo;
    }

    /**
     * Переключение на базу данных.
     * @param string имя базы данных
     */
    public function changeDbName(string $dbname)
    {
        if ($this->isOpen()) {
            $this->query("USE `$dbname`");
        }
        $this->dbname = $dbname;
    }


    // Работа с транзакциями

    /**
     * Проверка открытости транзакции.
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->getPdo()->inTransaction();
    }

    /**
     * Создание транзакции.
     * @return self
     */
    public function beginTransaction(): DatabaseInterface
    {
        $this->rollBack()->getPdo()->beginTransaction();
        return $this;
    }

    /**
     * Отмена транзакции.
     * @return self
     */
    public function rollBack(): DatabaseInterface
    {
        if (true === $this->inTransaction()) $this->getPdo()->rollBack();
        return $this;
    }

    /**
     * Коммит транзакции.
     * @return self
     */
    public function commit(): DatabaseInterface
    {
        if (true === $this->inTransaction()) $this->getPdo()->commit();
        return $this;
    }

    /**
     * Выполнение функции в транзакции с коммитом в конце.
     * @param \Closure колбек-функция для выполнения внутри транзакции
     * @return self
     */
    public function transaction(\Closure $callback): DatabaseInterface
    {
        $callback = $callback->bindTo($this);
        $this->beginTransaction();
        $callback();
        $this->commit();
        return $this;
    }



    // Работа с запросами

    /**
     * Получение подготовленного запроса.
     * @param string sql-запрос
     * @return PDOStatement подготовленный запрос
     * @throws DatabaseQueryException
     */
    public function prepare(string $sql): PDOStatement
    {
        // закрываем буффер последнего statement запроса
        if (!empty($this->lastStmt)) $this->lastStmt->closeCursor();
        try {
            $stmt = $this->getPdo()->prepare($sql);
        } catch (PDOException $e) {
            throw DatabaseQueryException::fromErrorInfo($this->errorInfo(), $sql);
        }
        return $stmt;
    }

    /**
     * Выполнение подготовленного запроса.
     * @param PDOStatement подготовленный запрос
     * @param array|null экранируемые параметры запроса
     * @return QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function execute(PDOStatement &$stmt, array $props = null): QueryResultInterface
    {
        try {
            if (false === $stmt->execute($props)) {
                $errorInfo = $stmt->errorInfo();
                $sql = $stmt->queryString;
                $stmt->closeCursor();
                throw DatabaseQueryException::fromErrorInfo($errorInfo, $sql, $props);
            }
        } catch (PDOException $e) {
            $sql = $stmt->queryString;
            throw DatabaseQueryException::fromErrorInfo($this->errorInfo(), $sql, $props);
        }
        return $this->getQueryResult($stmt);
    }

    /**
     * Выполнение запроса с возвражением количества измененных строк.
     * @param string sql-запрос
     * @return int
     * @throws DatabaseQueryException
     */
    public function pdoExec(string $sql): int
    {
        try {
            $rowCount = $this->getPdo()->exec($sql);
            if (false === $rowCount) {
                throw DatabaseQueryException::fromErrorInfo($this->errorInfo(), $sql); 
            }
        } catch (PDOException $e) {
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
        // закрываем буффер последнего statement запроса
        if (!empty($this->lastStmt)) $this->lastStmt->closeCursor();
        try {
            // перехватываем переключение базы данных для смены атрибута
            if (strpos($sql, 'USE') === 0 && in_array($sql[3] ?? '', [' ', '`'])) {
                $this->getPdo()->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
                $stmt = $this->getPdo()->query($sql);
                $this->getPdo()->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } else {
                $stmt = $this->getPdo()->query($sql);
            }
        } catch (PDOException $e) {
            throw DatabaseQueryException::fromErrorInfo($this->errorInfo(), $sql);
        }
        return $this->getQueryResult($stmt);
    }

    /**
     * Получение объекта результата запроса.
     * @param PDOStatement statement запроса
     * @return QueryResultInterface
     * @throws UnexpectedValueException
     */
    protected function getQueryResult(PDOStatement &$stmt): QueryResultInterface
    {
        $result = new $this->queryResultClass($stmt, $this);
        if (! $result instanceof QueryResultInterface) {
            $className = get_class($result);
            throw new UnexpectedValueException("Class\"$name\" must implements interface ".QueryResultInterface::class);
            
        }
        $this->lastStmt = &$stmt;
        return $result;
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
        if (empty($props)) return $this->pdoQuery($sql);
        $stmt = $this->prepare($sql);
        return $this->execute($stmt, $props);
    }

    /**
     * Выполнение нескольких запросов.
     * @param string sql-запросы разделённые ;
     * @param array|null экранируемые параметры запроса
     * @return array of QueryResultInterface
     * @throws DatabaseQueryException
     */
    public function batchQuery(string $batchSql, array $props = null): array
    {
        $sqls = explode(';', trim($batchSql));
        if (empty($sqls[count($sqls) - 1])) array_pop($sqls);
        $result = [];
        $this->beginTransaction();
        foreach ($sqls as &$sql) {
            $result[] = $this->query($sql, $props);
        }
        $this->commit();
        return $result;
    }


    /**
     * Установка функции экранирования объектов и массивов.
     * @param string строковая команда
     */
    public function setQuoteObjectsFunc(string $type = 'null')
    {
        $command = self::QUOTE_OBJECTS_FUNCS[$type] ?? null;
        if (!$command) {
            $command = self::QUOTE_OBJECTS_FUNCS['null'];
        }
        $this->quoteObjectsFunc = $command;
        return $this;
    }

    /**
     * Экранирование пользовательских данных для запроса.
     * @param mixed значение
     * @return string|numeric экранированное значение
     */
    public function quote($value)
    {
        if (null === $value) return 'NULL';
        if (is_numeric($value)) return $value;
        if (is_bool($value)) return intval($value);
        if (is_string($value)) return "'" . str_replace("'", '\\\'', $value) . "'";
        if (is_callable($value)) return 'NULL';
        eval('$value = '.$this->quoteObjectsFunc.'($value);');
        return $value;
    }

    /**
     * Получить расширенную информацию об ошибке последнего запроса.
     * @return array|null
     */
    public function errorInfo(): ?array
    {
        if (!$this->isOpen()) return null;
        return $this->getPdo()->errorInfo();
    }

    /**
     * Получение id последней вставленной записи.
     * @param string|null имя таблицы
     * @return int
     */
    public function lastInsertId(string $tbl = null): int
    {
        return intval($this->getPdo()->lastInsertId($tbl));
    }
}
