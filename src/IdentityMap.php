<?php
/**
 * Маппинг идентичности сущностей данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Db\Exceptions\IdentityMapException;
use Evas\Db\Interfaces\DatabaseInterface;

class IdentityMap
{
    /**
     * @var array хранилище объектов записей 
     *  [
     *      $className => [
     *          $primaryValue => [
     *              'object' => (object) $object, // актуальный объект записи
     *              'state' => (array) $state, // снимок последнего состояния объекта
     *          ], ...
     *      ], ...
     *  ]
     */
    protected $states = [];

    /** @var DatabaseInterface */
    protected $db;

    /**
     * Получение ключа объекта записи.
     * @param object
     * @param string первичный ключ
     * @return array
     * @throws IdentityMapException
     */
    public static function getKey(object &$object, string $primaryKey): array
    {
        $primary = $object->$primaryKey ?? null;
        $className = get_class($object);
        if (empty($primary)) {
            throw IdentityMapException::withInfo(
                'IdentityMap not found entity primary value', 
                compact('className', 'primaryKey')
            );
        }
        return [$className, $primary];
    }

    /**
     * Конструктор.
     * @param DatabaseInterface
     */
    public function __construct(DatabaseInterface &$db)
    {
        $this->db = $db;
    }

    /**
     * Установка объекта и состояния.
     * @param object
     * @param string первичный ключ
     * @return object
     * @throws IdentityMapException
     */
    public function set(object &$object, string $primaryKey): object
    {
        list($className, $primary) = static::getKey($object, $primaryKey);
        if (isset($this->states[$className][$primary])) {
            throw IdentityMapException::withInfo(
                'IdentityMap entity already has', 
                compact('className', 'primaryKey'), $primary
            );
        }
        $this->states[$className][$primary] = [
            'object' => &$object,
            'state' => get_object_vars($object),
        ];
        return $object;
    }

    /**
     * Обновление объекта и состояния.
     * @param object объект
     * @param string первичный ключ
     * @return object
     */
    public function update(object &$object, string $primaryKey): object
    {
        list($className, $primary) = static::getKey($object, $primaryKey);
        $old = $this->states[$className][$primary]['object'] ?? null;
        if (empty($old)) {
            $this->set($object, $primaryKey);
        } else {
            foreach ($object as $name => $value) {
                $old->$name = $value;
            }
            $this->states[$className][$primary]['state'] = get_object_vars($object);
            $object = &$old;
        }
        return $object;
    }

    /**
     * Получение состояния.
     * @param object объект
     * @param string первичный ключ
     * @return array|null
     */
    public function getState(object &$object, string $primaryKey): ?array
    {
        list($className, $primary) = static::getKey($object, $primaryKey);
        return $this->states[$className][$primary]['state'] ?? null;
    }

    /**
     * Удаление объекта и состояния.
     * @param object объект
     * @param string первичный ключ
     * @return self
     */
    public function unset(object &$object, string $primaryKey)
    {
        list($className, $primary) = static::getKey($object, $primaryKey);
        unset($this->states[$className][$primary]);
    }

    /**
     * Получение всего маппинга объектов с состояниями.
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * Очистка всего маппинга объектов с состояниями.
     */
    public function clearStates()
    {
        return $this->states = [];
    }
}
