<?php
/**
 * Тест результата запроса.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests\QueryResultTest;
class User {}

namespace Evas\Db\tests;

use Codeception\Util\Autoload;
use Evas\Db\Base\QueryResult;
use Evas\Db\tests\help\DatabaseTestUnit;

Autoload::addNamespace('Evas\\Db', 'vendor/evas-php/evas-db/src');
Autoload::addNamespace('Evas\\Db\\tests', 'vendor/evas-php/evas-db/src/tests');

use Evas\Db\tests\QueryResultTest\User;

class QueryResultTest extends DatabaseTestUnit
{
    /**
     * @var QueryResult
     */
    private $qr_insert;
    private $qr_select;

    /**
     * Утсанавливаем QueryResult.
     */
    protected function _before()
    {
        $db = $this->db();
        $db->beginTransaction();
        // отлючаем строгую проверку первичных ключей
        $db->notStrictPrimary();
        $this->qr_insert = $db->insert('users', static::TEST_USER_DATA);
        $this->qr_select = $db->select('users', 'name, email')->query();
        $this->assertTrue($this->qr_insert instanceof QueryResult);
        $this->assertTrue($this->qr_select instanceof QueryResult);
        $db->rollBack();
    }

    /**
     * Тест получения statement ответа базы.
     */
    public function testGetStmt()
    {
        $this->assertNotEmpty($this->qr_insert);
        $this->assertNotEmpty($this->qr_select);
    }

    /**
     * Тест получения количества возвращённых строк.
     */
    public function testRowCount()
    {
        $this->assertEquals(1, $this->qr_insert->rowCount());
        $this->assertEquals(1, $this->qr_select->rowCount());
    }

    /**
     * Тест получения записи в виде массива.
     */
    public function testNumericArray()
    {
        $this->assertEquals(
            [static::TEST_USER_DATA['name'], static::TEST_USER_DATA['email']],
            $this->qr_select->numericArray()
        );
    }

    /**
     * Тест получения всех записей в виде массива массивов.
     */
    public function testNumericArrayAll()
    {
        $this->assertEquals(
            [static::TEST_USER_DATA['name'], static::TEST_USER_DATA['email']],
            $this->qr_select->numericArrayAll()[0]
        );
    }

    /**
     * Тест получения записи в виде ассоциативного массива.
     */
    public function testAssocArray()
    {
        $this->assertEquals(
            static::TEST_USER_DATA,
            $this->qr_select->assocArray()
        );
    }

    /**
     * Тест получения всех записей в виде массива ассоциативных массивов.
     */
    public function testAssocArrayAll()
    {
        $this->assertEquals(
            static::TEST_USER_DATA,
            $this->qr_select->assocArrayAll()[0]
        );
    }

    /**
     * Тест получения записи в виде анонимного объекта.
     */
    public function testAnonymObject()
    {
        $this->assertEquals(
            (object) static::TEST_USER_DATA,
            $this->qr_select->anonymObject()
        );
    }

    /**
     * Тест получения всех записей в виде массива анонимных объектов.
     */
    public function testAnonymObjectAll()
    {
        $this->assertEquals(
            (object) static::TEST_USER_DATA,
            $this->qr_select->anonymObjectAll()[0]
        );
    }

    /**
     * Тест получения записи в виде объекта класса.
     */
    public function testClassObject()
    {
        $result = $this->qr_select->classObject(User::class);
        $this->assertTrue($result instanceof User);
        $this->assertEquals(static::TEST_USER_DATA, get_object_vars($result));
    }

    /**
     * Тест получения всех записей в виде объектов класса.
     */
    public function testClassObjectAll()
    {
        $result = $this->qr_select->classObjectAll(User::class);
        $this->assertTrue(is_array($result));
        $this->assertTrue($result[0] instanceof User);
        $this->assertEquals(static::TEST_USER_DATA, get_object_vars($result[0]));
    }

    /**
     * Тест добавления параметров записи в объект.
     */
    public function testIntoObject()
    {
        $user = new User;
        $result = $this->qr_select->intoObject($user);
        $this->assertEquals($result->email, $user->email);
        $this->assertEquals(static::TEST_USER_DATA, get_object_vars($result));
        $this->assertEquals(static::TEST_USER_DATA, get_object_vars($user));
        $this->assertEquals($user, $result);
    }
}
