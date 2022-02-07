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
            throw new \RuntimeException('DatabasesManager not has last connection');
        }
        return $this->connections[$this->lastName] 
        ?? throw new \RuntimeException("Database with publicName $this->lastName not found");
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
        throw new \RuntimeException("Database with publicName $name not found");
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
        throw new \RuntimeException("Database with publicName $name not found");
    }

    /**
     * Установка соединения.
     * @param DatabaseInterface|array соединение или конфиг соединения
     * @return self
     * @throws \RuntimeException
     */
    public function set($db)
    {
        if (is_array($db)) {
            return $this->set(new Database($db));
        } else if ($db instanceof DatabaseInterface) {
            if (!isset($db->publicName)) {
                if (count($this->connections) < 1) {
                    $db->publicName = 'default';
                } else {
                    throw new \RuntimeException("Database connection not has publicName property for Databases Manager");
                }
            }
            $this->connections[$db->publicName] = &$db;
            $this->setLast($db->publicName);
            return $this;
        }
        throw new \RuntimeException(sprintf(
            'Argument 1 passed to %s() must be an array or an instance of %s',
            __METHOD__, DatabaseInterface::class
        ));
    }
}
