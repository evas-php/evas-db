<?php
/**
 * Тест сборщика запросов.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests;

use Codeception\Util\Autoload;
use Evas\Db\Base\QueryResult;
use Evas\Db\Builders\JoinBuilder;
use Evas\Db\Builders\QueryBuilder;
use Evas\Db\Exceptions\DatabaseQueryException;
use Evas\Db\Exceptions\QueryBuilderException;
use Evas\Db\tests\help\DatabaseTestUnit;

Autoload::addNamespace('Evas\\Db', 'vendor/evas-php/evas-db/src');
Autoload::addNamespace('Evas\\Db\\tests', 'vendor/evas-php/evas-db/src/tests');

class QueryBuilderTest extends DatabaseTestUnit
{
    // Вспомогательные свойства и методы

    const STRING_JOIN = ' JOIN users ON users.id = auths.user_id';

    /**
     * Помощник проверки join'ов.
     * @param string тип join'а
     * @param QueryBuilder
     */
    protected function checkJoinBuilder(string $type, $jb)
    {
        $this->assertTrue($jb instanceof JoinBuilder);
        $qb = $jb->on('users.id = auths.user_id');
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals(strtoupper($type) . static::STRING_JOIN, $qb->join[0]);
    }

    protected function _before()
    {
        parent::_before();
        // вставляем данные пользователя для тестов
        $this->insertUserData();
        $this->isInsertUserData();
    }

    // Тесты

    /**
     * Тест сборки начала SELECT запроса.
     */
    public function testSelect()
    {
        // начинаем сборку запроса
        $qb = $this->db()->select('users', 'name, email');
        $this->assertTrue($qb instanceof QueryBuilder);
        // заканчиваем сборку выполнением запроса
        $qr = $qb->query();
        $this->assertTrue($qr instanceof QueryResult);
        // убеждаемся в верности найденной записи
        $this->assertEquals(1, $qr->rowCount());
        $this->assertEquals(static::TEST_USER_DATA, $qr->assocArray());
    }

    /**
     * Тест сборки начала DELETE запроса.
     */
    public function testDelete()
    {
        // запрашиваем запись, их 1 - запись есть
        $this->assertEquals(1, $this->db()->select('users')->one()->rowCount());
        // начинаем сборку запроса
        $qb = $this->db()->delete('users');
        $this->assertTrue($qb instanceof QueryBuilder);
        // добавляем условие поиска для удаления
        $qb = $qb->where('id = ?', [1]);
        // заканчиваем сборку выполнением запроса с установкой LIMIT 1
        $qr = $qb->one();
        $this->assertTrue($qr instanceof QueryResult);
        // убеждаемся в верности удаления записи
        // затронута 1 строка
        $this->assertEquals(1, $qr->rowCount());
        // запрашиваем запись, их 0 - запись удалена
        $this->assertEquals(0, $this->db()->select('users')->one()->rowCount());
    }

    /**
     * Тест сборки начала UPDATE запроса.
     */
    public function testUpdate()
    {
        // начинаем сборку запроса
        $qb = $this->db()->update('users', [
            'name' => 'Egor2',
        ]);
        $this->assertTrue($qb instanceof QueryBuilder);
        // добавляем условие поиска для обновления
        $qb = $qb->where('id = ?', [1]);
        // заканчиваем сборку выполнением запроса с установкой LIMIT 1
        $qr = $qb->one();
        $this->assertTrue($qr instanceof QueryResult);
        // убеждаемся в верности обновления записи
        // затронута 1 строка
        $this->assertEquals(1, $qr->rowCount());
        // запрашиваем запись - запись обновлена
        $qr = $this->db()->select('users', 'name, email')->where('id = ?', [1])->one();
        $this->assertTrue($qr instanceof QueryResult);
        $this->assertEquals(
            array_merge(static::TEST_USER_DATA, ['name' => 'Egor2']), 
            $qr->assocArray()
        );
    }

    /**
     * Тест установки части FROM.
     */
    public function testFrom()
    {
        $db = $this->db();
        // создаем сборку запроса
        $qb = new QueryBuilder($db);
        // устанавливаем FROM
        $from = 'SELECT name, email FROM users';
        $qb = $qb->from($from);
        // проверям установку FROM
        $this->assertEquals($from, $qb->from);
    }

    /**
     * Тест запуска сборщика INNER JOIN.
     */
    public function testInnerJoin()
    {
        $jb = $this->db()->select('auths')->innerJoin('users');
        $this->checkJoinBuilder('INNER', $jb);
    }

    /**
     * Тест запуска сборщика LEFT JOIN.
     */
    public function testLeftJoin()
    {
        $jb = $this->db()->select('auths')->leftJoin('users');
        $this->checkJoinBuilder('LEFT', $jb);
    }

    /**
     * Тест запуска сборщика RIGHT JOIN.
     */
    public function testRightJoin()
    {
        $jb = $this->db()->select('auths')->rightJoin('users');
        $this->checkJoinBuilder('RIGHT', $jb);
    }

    /**
     * Тест запуска сборщика OUTER JOIN.
     */
    public function testOuterJoin()
    {
        $jb = $this->db()->select('auths')->outerJoin('users');
        $this->checkJoinBuilder('OUTER', $jb);
    }

    /**
     * Тест запуска сборщика INNER JOIN, через алиас join.
     */
    public function testJoin()
    {
        $jb = $this->db()->select('auths')->join('users');
        $this->checkJoinBuilder('INNER', $jb);
    }

    /**
     * Тест добавление JOIN.
     */
    public function testSetJoin()
    {
        $qb = $this->db()->select('auths')->setJoin(static::STRING_JOIN);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals(static::STRING_JOIN, $qb->join[0]);
    }


    /**
     * Тест установки WHERE.
     */
    public function testWhere()
    {
        $where = 'id = ?';
        $values = [1];
        $qb = $this->db()->select('users', 'name, email')->where($where, $values);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals($where, $qb->where);
        $this->assertEquals($values, $qb->values);
        $this->assertEquals(static::TEST_USER_DATA, $qb->query()->assocArray());
    }

    /**
     * Тест установки WHERE IN.
     */
    public function testWhereIn()
    {
        $qb = $this->db()->select('users', 'name, email')->whereIn('id', [1]);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals('id IN (?)', $qb->where);
        $this->assertEquals([1], $qb->values);
        $this->assertEquals(static::TEST_USER_DATA, $qb->query()->assocArray());
    }


    /**
     * Тест установки GROUP BY.
     */
    public function testGroupBy()
    {
        $qb = $this->db()->select('users', 'name')->groupBy('name');
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals('name', $qb->groupBy);
        $expected = ['name' => static::TEST_USER_DATA['name']];
        $this->assertEquals($expected, $qb->query()->assocArray());
    }

    /**
     * Тест установки ORDER BY.
     */
    public function testOrderBy()
    {
        // ORDER BY name ASC
        $qb = $this->db()->select('users', 'name, email')->orderBy('name');
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals('name', $qb->orderBy);
        $this->assertEquals(static::TEST_USER_DATA, $qb->query()->assocArray());
        // ORDER BY name DESC
        $qb = $this->db()->select('users', 'name, email')->orderBy('name', true);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals('name', $qb->orderBy);
        $this->assertTrue($qb->orderDesc);
    }

    /**
     * Тест установки OFFSET.
     */
    public function testOffset()
    {
        // использование offset вместе с limit через отдельный метод offset()
        $qb = $this->db()->select('users', 'name, email')->limit(1)->offset(1);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals(1, $qb->offset);
        $this->assertEquals(0, $qb->query()->rowCount());

        // использование offset вместе с limit через 2 аргумент метода limit()
        $qb = $this->db()->select('users', 'name, email')->limit(1, 1);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals(1, $qb->offset);
        $this->assertEquals(0, $qb->query()->rowCount());

    }

    /**
     * Тест выброса исключений при установке OFFSET и LIMIT.
     */
    public function testLimitAndOffsetException()
    {
        // limit должен быть больше 0
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('Query limit must be more than 0');
        $qb = $this->db()->select('users', 'name, email')->limit(0);

        // offset должен быть больше 0
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('Query offset must be more than 0');
        $qb = $this->db()->select('users', 'name, email')->offset(0);

        // offset должен использоваться с limit
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('Query offset must be used with limit');
        $qb = $this->db()->select('users', 'name, email')->offset(1)->query();
    }

    /**
     * Тест установки LIMIT.
     */
    public function testLimit()
    {
        // установка LIMIT
        $qb = $this->db()->select('users', 'name, email')->limit(1);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals(1, $qb->limit);
        $this->assertEquals(1, $qb->query()->rowCount());
        $this->assertEquals(static::TEST_USER_DATA, $qb->query()->assocArray());
        // установка LIMIT и OFFSET
        $qb = $this->db()->select('users', 'name, email')->limit(1, 2);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals(1, $qb->limit);
        $this->assertEquals(2, $qb->offset);
        $this->assertEquals(0, $qb->query()->rowCount());
    }


    /**
     * Тест получения sql.
     */
    public function testGetSql()
    {
        $token = 'sample_token';
        $qb = $this->db()->select('auths', 'users.name, users.email')
            ->setJoin(static::STRING_JOIN)
            ->where('token = ?', [$token])
            ->groupBy('name')
            ->orderBy('name', true)
            ->limit(1, 1);
        $this->assertTrue($qb instanceof QueryBuilder);
        $sql = $qb->getSql();
        $this->assertEquals('SELECT users.name, users.email FROM `auths` ' . static::STRING_JOIN . ' WHERE token = ? GROUP BY name ORDER BY name DESC LIMIT 1 OFFSET 1', $sql);
    }

    /**
     * Тест добавления значения.
     */
    public function testBindValue()
    {
        $qb = $this->db()->select('users')
            ->where('id = ?')
            ->bindValue(1);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals([1], $qb->getValues());
    }

    /**
     * Тест добавления значений.
     */
    public function testBindValues()
    {
        $qb = $this->db()->select('users')
            ->where('action = ? AND status = ?')
            ->bindValues([1, 2]);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals([1,2], $qb->getValues());
    }

    /**
     * Тест получения значений.
     */
    public function testGetValues()
    {
        $qb = $this->db()->select('users')
            ->where('id > ? AND action = ? AND status = ?')
            ->bindValue(1)
            ->bindValues([1,2]);
        $this->assertTrue($qb instanceof QueryBuilder);
        $this->assertEquals([1,1,2], $qb->getValues());
    }

    /**
     * Тест получения одной записи.
     */
    public function testOne()
    {
        $this->db()->insert('users', array_merge(static::TEST_USER_DATA, ['email' => 'sample']));
        $qr1 = $this->db()->select('users', 'name, email')->limit(1)->query();
        $qr2 = $this->db()->select('users', 'name, email')->one();
        $this->assertEquals(1, $qr2->rowCount());
        $this->assertEquals($qr1->rowCount(), $qr2->rowCount());
        $this->assertEquals(static::TEST_USER_DATA, $qr2->assocArray());
    }

    /**
     * Тест получения записей.
     */
    public function testQuery()
    {
        $qr = $this->db()->select('users', 'name, email')->query();
        $this->assertTrue($qr instanceof QueryResult);
        $this->assertEquals(1, $qr->rowCount());
        $this->assertEquals(static::TEST_USER_DATA, $qr->assocArray());
    }
}
