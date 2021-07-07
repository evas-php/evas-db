<?php
/**
 * Обёртка unit-тестов класса Database.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests\help;

use Codeception\Util\Autoload;
use Evas\Db\Database;
use Evas\Db\Interfaces\DatabaseInterface;
use Evas\Db\tests\help\GlobalDb;

Autoload::addNamespace('Evas\\Db\\tests\\help', 'vendor/evas-php/evas-db/src/tests/help');

class DatabaseTestUnit extends \Codeception\Test\Unit
{
    const TEST_USER_DATA = [
        'name' => 'Egor',
        'email' => 'egor@evas-php.com',
    ];

    public static function config()
    {
        return GlobalDb::config();
    }

    public static function staticDb(): DatabaseInterface
    {
        return GlobalDb::staticDb();
    }

    protected function db(): DatabaseInterface
    {
        return static::staticDb();
    }

    protected function _before()
    {
        static::staticDb();
        $this->beginTransaction();
    }

    protected function _after()
    {
        $this->rollback();
    }

    /**
     * Начало транзакции.
     */
    protected function beginTransaction()
    {
        // $this->assertFalse($this->db()->inTransaction());
        $this->db()->beginTransaction();
        $this->assertTrue($this->db()->inTransaction());
    }

    /**
     * Откат транзакции.
     */
    protected function rollback()
    {
        // $this->assertTrue($this->db()->inTransaction());
        $this->db()->rollback();
        $this->assertFalse($this->db()->inTransaction());
        // сбрасываем автоинкремент
        // $this->db()->query('ALTER TABLE users AUTO_INCREMENT=1');
        $this->db()->query('ALTER TABLE users AUTO_INCREMENT='.$this->db()->maxId('users'));
    }

    /**
     * Применение транзакции.
     */
    protected function commit()
    {
        $this->assertTrue($this->db()->inTransaction());
        $this->db()->commit();
        $this->assertFalse($this->db()->inTransaction());
    }

    /**
     * Добавление записи.
     */
    protected function insertUserData()
    {
        $qr = $this->db()->insert('users', static::TEST_USER_DATA);
        $this->assertEquals(1, $qr->rowCount());
        // $this->lastInsertId = $this->db()->lastInsertId();
    }

    /**
     * Проверка вставки записи.
     */
    protected function isInsertUserData()
    {
        $expected = array_merge(static::TEST_USER_DATA, ['id' => 1]);
        $actual = $this->db()->select('users')->one()->assocArray();
        $this->assertEquals($expected, $actual);
        // $qr = $this->db()->select('users')->where('id = ?', [$id])->one();
        // $this->assertEquals($expected, $qr->assocArray());
    }
}
