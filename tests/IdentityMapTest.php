<?php
/**
 * Тест маппинга идентичности сущностей данных.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests\IdentityMapTest;
use Evas\Db\tests\help\DatabaseTestUnit;
class User
{
    public $id;
    public $name;
    public $email;

    public function __construct(array $data = null)
    {
        if ($data) foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function state()
    {
        return DatabaseTestUnit::staticDb()->identityMap()->getState($this, 'id');
    }

    public function hash()
    {
        return spl_object_hash($this);
    }
}

namespace Evas\Db\tests;

use Codeception\Util\Autoload;
use Evas\Db\Base\QueryResult;
use Evas\Db\Database;
use Evas\Db\IdentityMap;
use Evas\Db\tests\help\DatabaseTestUnit;

Autoload::addNamespace('Evas\\Db', 'vendor/evas-php/evas-db/src');
Autoload::addNamespace('Evas\\Db\\tests\\help', 'vendor/evas-php/evas-db/src/tests/help');

use Evas\Db\tests\IdentityMapTest\User;

class IdentityMapTest extends DatabaseTestUnit
{
    // Вспомогательные свойства и методы

    const UPDATED_USER_DATA = [
        'name' => 'Updated name',
        'email' => 'Updated email',
    ];

    // Тесты

    /**
     * Тест методов IdentityMap.
     */
    public function testIdentityMap()
    {
        // data
        $state0 = array_merge(static::TEST_USER_DATA, ['id' => 1]);
        $user0 = (object) $state0;

        // make IdentityMap
        $db = $this->db();
        $identityMap = new IdentityMap($db);
        $this->assertEmpty($identityMap->getState($user0, 'id'));

        // check IdentityMap getStates() & clearStates()
        $this->assertEmpty($identityMap->getStates());
        $identityMap->set($user0, 'id');
        $this->assertNotEmpty($identityMap->getState($user0, 'id'));
        $this->assertEquals($state0, $identityMap->getState($user0, 'id'));
        $expectedStates = [
            \stdClass::class => [
                1 => [
                    'object' => $user0,
                    'state' => $state0,
                ]
            ]
        ];
        $this->assertEquals($expectedStates, $identityMap->getStates());
        $identityMap->clearStates();
        $this->assertEmpty($identityMap->getStates());
        
        // set $user0 to IdentityMap
        $identityMap->set($user0, 'id');
        $this->assertNotEmpty($identityMap->getState($user0, 'id'));
        $this->assertEquals($state0, $identityMap->getState($user0, 'id'));

        // update $user0 in IdentityMap
        $state1 = array_merge(static::UPDATED_USER_DATA, ['id' => 1]);
        $user1 = (object) $state1;
        $identityMap->update($user1, 'id');
        $this->assertNotEquals($state0, $identityMap->getState($user0, 'id'));
        $this->assertNotEquals($state0, $identityMap->getState($user1, 'id'));
        $this->assertEquals($state1, $identityMap->getState($user0, 'id'));
        $this->assertEquals($state1, $identityMap->getState($user1, 'id'));
        $this->assertEquals($user0, $user1);

        // unset $user0 from IdentityMap
        $identityMap->unset($user0, 'id');
        $this->assertEmpty($identityMap->getState($user0, 'id'));

        $expectedStates = [
            \stdClass::class => []
        ];
        $this->assertEquals($expectedStates, $identityMap->getStates());
    }

    protected function getUser()
    {
        return $this->db()->select('users')->where('id = ?', [1])->one()->classObject(User::class);
    }

    /**
     * Тест IdentityMap внутри Database.
     */
    public function testDbIdentityMap()
    {
        $state0 = array_merge(static::TEST_USER_DATA, ['id' => 1]);
        $user0 = new User($state0);

        $this->insertUserData();
        $this->isInsertUserData();
        $user1 = $this->getUser();
        $user2 = $this->getUser();
        $this->assertEquals('Egor', $user1->name);
        // $this->assertEquals('Egor2', $user1->name);

        $this->assertEquals($user0, $user1);
        $this->assertEquals($user0, $user2);
        $this->assertEquals($user1, $user2);
        
        $this->assertEquals($state0 = $user0->state(), $state1 = $user1->state());
        $this->assertEquals($user0->state(), $state2 = $user2->state());
        $this->assertEquals($user1->state(), $user2->state());

        $this->assertNotEquals($hash0 = $user0->hash(), $hash1 = $user1->hash());
        $this->assertNotEquals($hash0, $hash2 = $user2->hash());
        $this->assertEquals($hash1, $hash2);

        // Update
        $updatedCount = $this->db()->update('users', static::UPDATED_USER_DATA)->where('id = ?', [1])->one()->rowCount();
        $this->assertEquals(1, $updatedCount);
        $user3 = $this->getUser();

        $this->assertNotEquals($user3, $user0);
        $this->assertEquals($user3, $user1);
        $this->assertEquals($user3, $user2);

        $this->assertNotEquals($hash3 = $user3->hash(), $hash0);
        $this->assertEquals($hash3, $hash1);
        $this->assertEquals($hash3, $hash2);

        $this->assertNotEquals($state3 = $user3->state(), $state0);
        $this->assertNotEquals($state3, $state1);
        $this->assertNotEquals($state3, $state2);

        $this->assertEquals($state3, $user0->state());
        $this->assertEquals($state3, $user1->state());
        $this->assertEquals($state3, $user2->state());
    }
}
