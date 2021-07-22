<?php
/**
 * Тест транзакций базы данных.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests;

use Codeception\Util\Autoload;
use Evas\Db\tests\help\DatabaseTestUnit;

Autoload::addNamespace('Evas\\Db', 'vendor/evas-php/evas-db/src');
Autoload::addNamespace('Evas\\Db\\tests', 'vendor/evas-php/evas-db/src/tests');

class DatabaseTransactionsTest extends DatabaseTestUnit
{
    // Вспомогательные свойства и методы

    protected function _before()
    {
        static::staticDb();
    }

    // Тесты

    /**
     * Тест создания транзакии.
     */
    public function testTransactionBegin()
    {
        $this->assertFalse($this->db()->inTransaction());
        $this->db()->beginTransaction();
        $this->assertTrue($this->db()->inTransaction());
    }

    /**
     * Тест проверки нахождения в транзакции.
     */
    public function testInTransaction()
    {
        $this->assertFalse($this->db()->inTransaction());
        $this->db()->beginTransaction();
        $this->assertTrue($this->db()->inTransaction());
    }

    /**
     * Тест коммита транзакции.
     */
    public function testTransactionCommit()
    {
        $this->assertFalse($this->db()->inTransaction());
        // создаем транзакцию
        $this->db()->beginTransaction();
        $this->assertTrue($this->db()->inTransaction());
        
        // добавляем запись
        $this->insertUserData();
        
        // коммитим транзакцию
        $this->db()->commit();
        $this->assertFalse($this->db()->inTransaction());
        
        // проверяем наличие записи
        $this->isInsertUserData();

        // удаляем запись и устанавливаем auto_increment = 1
        $this->assertEquals(1, $this->db()->delete('users')->where('id = ?', [1])->one()->rowCount());
        $this->db()->query('ALTER TABLE users AUTO_INCREMENT=1');
    }

    /**
     * Тест отката транзакции.
     */
    public function testTransactionRollback()
    {
        $this->assertFalse($this->db()->inTransaction());
        // создаем транзакцию
        $this->db()->beginTransaction();
        $this->assertTrue($this->db()->inTransaction());
        
        // добавляем запись
        $this->insertUserData();
        // проверяем наличие записи
        $this->isInsertUserData();

        // откатываем транзакцию
        $this->db()->rollback();
        $this->assertFalse($this->db()->inTransaction());
        $this->assertEquals(0, $this->db()->select('users')->query()->rowCount());
        $this->db()->query('ALTER TABLE users AUTO_INCREMENT=1');
    }

    /**
     * Тест авто отката транзакции при открытитии новой транзакции.
     */
    public function testTransactionAutoRollback()
    {
        $this->assertFalse($this->db()->inTransaction());
        // создаем транзакцию
        $this->db()->beginTransaction();
        $this->assertTrue($this->db()->inTransaction());
        
        // добавляем запись
        $this->insertUserData();
        // проверяем наличие записи
        $this->isInsertUserData();

        // запускаем новую транзакцию, предыдущая откатывается
        $this->db()->beginTransaction();
        $this->assertTrue($this->db()->inTransaction());
        $this->assertEquals(0, $this->db()->select('users')->query()->rowCount());

        // откатываем транзакцию
        $this->db()->rollback();
        $this->assertFalse($this->db()->inTransaction());
        $this->db()->query('ALTER TABLE users AUTO_INCREMENT=1');
    }
}
