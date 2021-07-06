<?php
/**
 * Тест сборщика join.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests;

use Codeception\Util\Autoload;
use Evas\Db\Base\QueryResult;
use Evas\Db\Builders\JoinBuilder;
use Evas\Db\Builders\QueryBuilder;
use Evas\Db\tests\help\DatabaseTestUnit;

Autoload::addNamespace('Evas\\Db', 'vendor/evas-php/evas-db/src');
Autoload::addNamespace('Evas\\Db\\tests\\help', 'vendor/evas-php/evas-db/src/tests/help');

class JoinBuilderTest extends DatabaseTestUnit
{
    // Вспомогательные свойства и методы
    
    protected function newQueryBuilder()
    {
        $db = $this->db();
        return new QueryBuilder($db);
    }

    protected function newJoinBuilder() 
    {
        $qb = $this->newQueryBuilder();
        return new JoinBuilder($qb);
    }

    // Тесты

    /**
     * Тест конструтора сборщика.
     */
    public function testConstruct()
    {
        $qb = $this->newQueryBuilder();
        $jb = new JoinBuilder($qb);
        $this->assertTrue($jb instanceof JoinBuilder);
        $this->assertEmpty($jb->type);
        $this->assertEmpty($jb->from);
        // установка типа LEFT
        $jb = new JoinBuilder($qb, 'left');
        $this->assertTrue($jb instanceof JoinBuilder);
        $this->assertEquals('left', $jb->type);
        $this->assertEmpty($jb->from);
        // установка FROM
        $jb = new JoinBuilder($qb, null, 'users');
        $this->assertTrue($jb instanceof JoinBuilder);
        $this->assertEmpty($jb->type);
        $this->assertEquals('users', $jb->from);
    }

    /**
     * Тест установки from.
     */
    public function testFrom()
    {
        $tbl = 'users';
        $jb = $this->newJoinBuilder()->from($tbl);
        $this->assertEquals($tbl, $jb->from);

        $tbl = 'SELECT * FROM users WHERE role = ?';
        $values = ['admin'];
        $jb = $this->newJoinBuilder()->from($tbl, $values);
        $this->assertEquals($tbl, $jb->from);
        $this->assertEquals($values, $jb->getValues());
    }

    /**
     * Тест установки псевдонима выборки таблицы.
     */
    public function testAs()
    {
        $as = 'users';
        $jb = $this->newJoinBuilder()->as($as);
        $this->assertEquals($as, $jb->as);
    }

    /**
     * Тест установки сравнения.
     */
    public function testOn()
    {
        $on = 'users.id = auths.user_id';
        $qb = $this->newJoinBuilder()->on($on);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals(" JOIN  ON $on", $qb->join[0]);
    }

    /**
     * Тест получения sql.
     */
    public function testGetSql()
    {
        $tbl = 'SELECT * FROM users WHERE role = ?';
        $values = [1];
        $as = 'users';
        $jb = $this->newJoinBuilder()->from($tbl, $values)->as($as);
        $this->assertEquals(" JOIN ($tbl) AS $as", $jb->getSql());
        $this->assertEquals($values, $jb->getValues());
    }

    /**
     * Тест завершения сборки join'а.
     */
    public function testEndJoin()
    {
        $qb = $this->newJoinBuilder()->endJoin();
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals(" JOIN ", $qb->join[0]);
    }

    /**
     * Тест реального join запроса.
     */
    public function testQueryWithJoin()
    {
        $this->beginTransaction();
        $qr = $this->db()->select('users')->leftJoin('auths')->on('users.id = auths.user_id')->query();
        $this->assertTrue($qr instanceof QueryResult);
        $this->assertEquals(0, $qr->rowCount());

        $this->insertUserData();
        $user_id = $this->db()->lastInsertId('users');
        $this->db()->insert('auths', ['user_id' => $user_id]);

        $qr = $this->db()->select('users')->leftJoin('auths')->on('users.id = auths.user_id')->query();
        $this->assertTrue($qr instanceof QueryResult);
        $this->assertEquals(1, $qr->rowCount());
        $this->assertEquals($user_id, $qr->assocArray()['user_id']);
        $this->rollback();
    }
}
