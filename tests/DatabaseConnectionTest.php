<?php
/**
 * Тест соединения с базой данных.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests;

use Codeception\Util\Autoload;
use Evas\Db\Database;
use Evas\Db\Exceptions\DatabaseConnectionException;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\tests\help\DatabaseTestUnit;

Autoload::addNamespace('Evas\\Db', 'vendor/evas-php/evas-db/src');
Autoload::addNamespace('Evas\\Db\\tests', 'vendor/evas-php/evas-db/src/tests');

class DatabaseConnectionTest extends DatabaseTestUnit
{
    // Вспомогательные свойства и методы

    protected $db;

    public function db(): DatabaseInterface
    {
        if (!$this->db) $this->db = new Database(static::config());
        return $this->db;
    }

    protected function _before()
    {
        static::staticDb();
    }

    protected function _after()
    {}

    // Тесты

    /**
     * Тест открытия соединения.
     */
    public function testOpen()
    {
        $this->assertFalse($this->db()->isOpen());
        $this->db()->open();
        $this->assertTrue($this->db()->isOpen());
    }

    /**
     * Тест исключения открытия соединения.
     */
    public function testOpenException()
    {
        $this->assertFalse($this->db()->isOpen());
        $this->db()->username = 'undefined';
        $this->expectException(DatabaseConnectionException::class);
        $this->db()->open();
    }

    /**
     * Тест закрытия соединения.
     */
    public function testClose()
    {
        $this->db()->open();
        $this->assertTrue($this->db()->isOpen());
        $this->db()->close();
        $this->assertFalse($this->db()->isOpen());
    }

    /**
     * Тест автоматического отктытия соединения при первом запросе.
     */
    public function testAutoOpen()
    {
        // соединение открывается через обращение к методу query().
        $this->assertFalse($this->db()->isOpen());
        $this->db()->query('SELECT * FROM users LIMIT 1');
        $this->assertTrue($this->db()->isOpen());

        $this->db()->close();

        // соединение открывается через обращение к методу getPdo().
        $this->assertFalse($this->db()->isOpen());
        $this->assertNotEmpty($this->db()->getPdo());
        $this->assertTrue($this->db()->isOpen());
    }

    /**
     * Тест получения pdo-объекта.
     */
    public function testGetPdo()
    {
        $this->assertNotEmpty($this->db()->getPdo());
        $this->assertTrue($this->db()->getPdo() instanceof \pdo);
    }
}
