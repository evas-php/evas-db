<?php
/**
 * Трейт соединения базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\Exceptions\DatabaseConnectionException;

if (!defined('EVAS_DB_OPTIONS')) {
    define('EVAS_DB_OPTIONS', [
        \PDO::ATTR_EMULATE_PREPARES => false, // помогает с приведением типов из базы в php
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        // \PDO::ATTR_CASE => \PDO::CASE_LOWER,
        // \PDO::ATTR_AUTOCOMMIT => false,
        // \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        // \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
    ]);
}
if (!defined('EVAS_DB_DRIVER')) define('EVAS_DB_DRIVER', 'mysql');
if (!defined('EVAS_DB_HOST')) define('EVAS_DB_HOST', 'localhost');
if (!defined('EVAS_DB_CHARSET')) define('EVAS_DB_CHARSET', 'utf8');


trait DatabaseConnectionTrait
{
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

    /** @var \PDO */
    protected $pdo;

    /**
     * Открытие соединения.
     * @return self
     * @throws DatabaseConnectionException
     */
    public function open(): DatabaseInterface
    {
        $dsn = "$this->driver:host=$this->host";
        if (!empty($this->dbname)) $dsn .= ";dbname=$this->dbname";
        // if (!empty($this->charset)) $dsn .= ";charset=$this->charset";
        try {
            $this->pdo = new \PDO($dsn, $this->username, $this->password, $this->options);
        } catch (\PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage());
        }
        if ($this->charset) $this->setCharset($this->charset);
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
    public function getPdo(): \PDO
    {
        if (!$this->isOpen()) $this->open();
        return $this->pdo;
    }

    /**
     * Установка кодировки.
     * @param string кодировка
     * @return self
     */
    public function setCharset(string $charset)
    {
        $this->grammar()->setCharset($charset);
        return $this;
    }

    /**
     * Установка таймзоны.
     * @param string кодировка
     * @return self
     */
    public function setTimezone(string $timezone)
    {
        $this->grammar()->setTimezone($timezone);
        return $this;
    }

    /**
     * Переключение на базу данных.
     * @param string имя базы данных
     * @return self
     */
    public function changeDbName(string $dbname)
    {
        if ($this->isOpen()) $this->grammar()->changeDbName($dbname);
        $this->dbname = $dbname;
        return $this;
    }
}
