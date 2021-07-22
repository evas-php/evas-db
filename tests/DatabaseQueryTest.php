<?php
/**
 * Тест запросов базы данных.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests;

use Codeception\Util\Autoload;
use Evas\Db\Base\QueryResult;
use Evas\Db\Exceptions\DatabaseQueryException;
use Evas\Db\tests\help\DatabaseTestUnit;

Autoload::addNamespace('Evas\\Db', 'vendor/evas-php/evas-db/src');
Autoload::addNamespace('Evas\\Db\\tests', 'vendor/evas-php/evas-db/src/tests');

class DatabaseQueryTest extends DatabaseTestUnit
{
    /**
     * Тест экранирования значений.
     */
    public function testQuote()
    {
        $this->assertEquals('NULL', $this->db()->quote(null));
        $this->assertEquals(10, $this->db()->quote(10));
        $this->assertEquals("'hello'", $this->db()->quote('hello'));
        $this->assertEquals("'\\'hello\\' world'", $this->db()->quote("'hello' world"));
        $this->assertEquals('\'"hello" world\'', $this->db()->quote('"hello" world'));
        // тест экранирования объектов
        $subtestQuoteObject = function ($expected, $data) {
            $this->assertEquals($expected, $this->db()->quote($data));
        };
        $subtestQuoteObject->bindTo($this);

        $assocArray = static::TEST_USER_DATA;
        $numericArray = ['Jan', 'Feb', 'Mar'];
        $anonymObject = (object) $assocArray;

        $this->db()->setQuoteObjectsFunc();
        $subtestQuoteObject('NULL', $assocArray);
        $subtestQuoteObject('NULL', $numericArray);
        $subtestQuoteObject('NULL', $anonymObject);

        $this->db()->setQuoteObjectsFunc('json');
        $subtestQuoteObject(json_encode($assocArray), $assocArray);
        $subtestQuoteObject(json_encode($numericArray), $numericArray);
        $subtestQuoteObject(json_encode($anonymObject), $anonymObject);

        $this->db()->setQuoteObjectsFunc('serialize');
        $subtestQuoteObject(serialize($anonymObject), $anonymObject);
        $subtestQuoteObject(serialize($numericArray), $numericArray);
        $subtestQuoteObject(serialize($anonymObject), $anonymObject);
    }

    /**
     * Тест запроса в бд.
     */
    public function testQuery()
    {
        $qr = $this->db()->query('SELECT * FROM users LIMIT 1');
        $this->assertTrue($qr instanceof QueryResult);
        $this->assertEquals(0, $qr->rowCount());
        $this->assertEmpty($qr->assocArray());

        $keys = array_keys(static::TEST_USER_DATA);
        $vals = array_values(static::TEST_USER_DATA);
        $keys = implode('`, `', $keys);
        $vals = implode("', '", $vals);
        $qr = $this->db()->query("INSERT INTO users (`$keys`) VALUES ('$vals')");
        $this->assertEquals(1, $qr->rowCount());
    }

    /**
     * Тест исключения запроса к бд.
     */
    public function testQueryException()
    {
        $sql = 'SELECT * FROM users2 LIMIT 1';
        $errorData = [
            'error' => [
                'code' => 1146,
                'message' => "Table '{$this->db()->dbname}.users2' doesn't exist",
                'sqlState' => '42S02',
            ],
            'query' => $sql,
            'props' => [],
        ];
        $this->expectException(DatabaseQueryException::class);
        $this->expectExceptionMessage(json_encode($errorData));
        $this->db()->query($sql);
    }

    /**
     * Тест получения id последней вставленной записи.
     */
    public function testLastInsertId()
    {
        // создаем транзакцию
        // $this->assertEquals(null, $this->db()->query('SELECT MAX(id) FROM users'));
        $this->assertEquals(0, $this->db()->maxId('users'));
        // вставляем запись
        $this->insertUserData();
        // сверяем id вставленной записи
        $this->assertEquals(1, $this->db()->lastInsertId());
        $this->assertEquals(1, $this->db()->maxId('users'));
        // проверяем наличие записи
        $this->isInsertUserData();
        // после другого запроса lastInsertId обнуляется
        $this->assertEquals(0, $this->db()->lastInsertId());
        // откатываем транзакцию
        $this->rollback();
        $this->assertEquals(0, $this->db()->maxId('users'));
    }

    /**
     * Тест вставки записи.
     */
    public function testInsert()
    {
        // вставляем запись
        $this->insertUserData();
        // проверяем наличие записи
        $this->isInsertUserData();
    }

    /**
     * Тест вставки нескольких записей.
     */
    public function testBatchInsert()
    {
        // вставляем записи
        $data = [
            ['name' => 'Egor', 'email' => 'e.vasyakin@itevas.ru'],
            ['name' => 'Ivan', 'email' => 'another@mail.ru'],
        ];
        $this->assertEquals(2, $this->db()->batchInsert('users', $data)->rowCount());

        // проверяем наличие записей
        $ids = [1, 2];
        $this->assertEquals($data,
            $this->db()->select('users', 'name, email')
                ->whereIn('id', $ids)->limit(2)->query()->assocArrayAll()
        );
        $this->assertEquals(2, $this->db()->query('SELECT * FROM users')->rowCount());
    }

    /**
     * Тест поиска записи.
     */
    public function testSelect()
    {
        // записей нет
        $this->assertEquals(0, $this->db()->select('users')->query()->rowCount());
        // запись выше эквивалентна этой
        $this->assertEquals(0, $this->db()->query('SELECT * FROM users')->rowCount());

        // вставляем запись
        $this->insertUserData();
        // проверяем наличие записи
        $this->isInsertUserData();
        
        // два эквивалентых запроса
        $qr1 = $this->db()->query('SELECT * FROM users');
        // полученные строки буферизируются драйвером
        // и при следующем запросе с получением строк, буфер перезаписывается.
        // чтобы не потерять строки этого запроса, заносим их в переменную
        $users1 = $qr1->assocArrayAll(); // закидываем буфер в переменную
        $qr2 = $this->db()->select('users')->query();
        $this->assertEquals($qr1->rowCount(), $qr2->rowCount());
        $this->assertEquals($users1, $qr2->assocArrayAll());

        // мы можем указать ключи выборки второым аргументом
        // два эквивалентых запроса
        $qr1 = $this->db()->query('SELECT name, email FROM users');
        $users1 = $qr1->assocArrayAll(); // закидываем буфер в переменную
        $qr2 = $this->db()->select('users', 'name, email')->query();
        $this->assertEquals($qr1->rowCount(), $qr2->rowCount());
        $this->assertEquals($users1, $qr2->assocArrayAll());

        // select метод возвращет объект QueryBuilder, 
        // поэтому мы можем вызывать его методы, например where или one

        // два эквивалентых запроса
        $id = 1;
        $qr1 = $this->db()->query('SELECT * FROM users WHERE id = ? LIMIT 1', [$id]);
        $users1 = $qr1->assocArray();
        $qr2 = $this->db()->select('users')->where('id = ?', [$id])->one();
        $this->assertEquals($qr1->rowCount(), $qr2->rowCount());
        $this->assertEquals($users1, $qr2->assocArray());
    }

    /**
     * Тест обновления записи.
     */
    public function testUpdate()
    {
        // вставляем запись
        $this->insertUserData();
        // проверяем наличие записи
        $this->isInsertUserData();

        $id = 1;
        $data = ['name' => 'Egor2'];
        // вносим изменение и проверяем сколько строк было затронуто
        // update возвращает созданный объект QueryBuilder
        $this->assertEquals(1,
            $this->db()->update('users', $data)->where('id = ?', [$id])->one()->rowCount()
        );
        // проверяем изменилась ли запись
        $this->assertEquals(
            $this->db()->select('users', 'name')->where('id = ?', [$id])->one()->assocArray(),
            $data
        );
    }

    /**
     * Тест удаления записи.
     */
    public function testDelete()
    {
        // вставляем запись
        $this->insertUserData();
        // проверяем наличие записи
        $this->isInsertUserData();

        // проверяем количество записей в базе = 1
        $this->assertEquals(1, $this->db()->select('users')->query()->rowCount());

        // удаляем запись и проверяем сколько строк было затронуто
        $this->assertEquals(1,
            $this->db()->delete('users')->where('id = ?', [1])->one()->rowCount()
        );

        // проверяем количество записей в базе = 0
        $this->assertEquals(0, $this->db()->select('users')->query()->rowCount());
    }
}
