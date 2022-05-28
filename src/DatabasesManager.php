<?php
/**
 * Менеджер соединений с базами данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Database;
use Evas\Db\Interfaces\DatabaseInterface;

class DatabasesManager
{
    /** @var array маппинг соединений с базами данных */
    protected $connections = [];

    /** @var string имя последнего соединения */
    protected $lastName;

    /**
     * Получение имени последнего соединения.
     * @return string|null
     */
    public function lastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Получение последнего соединения или null.
     * @return DatabaseInterface
     * @throws \RuntimeException
     */
    public function last(): DatabaseInterface
    {
        if (!isset($this->lastName)) {
            $count = count($this->connections);
            if ($count < 1) {
                throw new \RuntimeException('DatabasesManager not has connections');
            }
            if ($count < 2) {
                return $this->setLast(array_keys($this->connections)[0])->last();
            }
            throw new \RuntimeException('DatabasesManager not has last connection');
        }
        return $this->connections[$this->lastName] 
        ?? throw new \RuntimeException("Database with name `$this->lastName` not found");
    }

    /**
     * Получение соединения по имени или последнего.
     * @param string|null имя соединения
     * @return DatabaseInterface|null
     * @throws \RuntimeException
     */
    public function get(string $name = null): DatabaseInterface
    {
        if (!$name) $name = $this->lastName;
        if ($this->has($name)) {
            return $this->connections[$name];
        }
        throw new \RuntimeException("Database with name `$name` not found");
    }

    /**
     * Проверка наличия соединения по имени.
     * @param string имя соединения
     * @return bool
     */
    public function has(string $name): bool
    {
        return in_array($name, array_keys($this->connections));
    }

    /**
     * Установка имени последнего соединения.
     * @param string имя соединения
     * @return self
     * @throws \RuntimeException
     */
    public function setLast(string $name)
    {
        if ($this->has($name)) {
            $this->lastName = $name;
            return $this;
        }
        throw new \RuntimeException("Database with name `$name` not found");
    }

    /**
     * Установка соединения.
     * @param string имя соединения
     * @param DatabaseInterface|array соединение или конфиг соединения
     * @return self
     * @throws \RuntimeException
     */
    public function set(string $name, $db)
    {
        if (is_array($db)) {
            return $this->set($name, new Database($db));
        } else if ($db instanceof DatabaseInterface) {
            $this->connections[$name] = &$db;
            $this->setLast($name);
            return $this;
        }
        throw new \RuntimeException(sprintf(
            'Argument 1 passed to %s() must be an array or an instance of %s',
            __METHOD__, DatabaseInterface::class
        ));
    }

    /**
     * Конструктор.
     * @param array|DatabaseInterface[] маппинг соединений или конфигов соединений по именам
     * @throws \RuntimeException
     */
    public function __construct(array $dbs = null)
    {
        if ($dbs) foreach ($dbs as $name => $db) {
            $this->set($name, $db);
        }
    }

    /**
     * Магический перехват вызовов методов соединения.
     * @param string имя метода
     * @param array|null аргументы
     */
    public function __call(string $name, array $args = [])
    {
        return $this->last()->$name(...$args);
    }

    /**
     * Хук перехвата аргументов di контейнера.
     * @param string|null имя соединения
     * @return self
     */
    public function __invoke(string $name = null)
    {
        return $name ? $this->setLast($name) : $this;
    }
}
