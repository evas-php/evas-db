<?php
/**
 * Трейт расширения базы данных поддержкой маппинга идентичности сущностей.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\IdentityMap;

trait DatabaseIdentityMapTrait
{
    /** @var IdentityMap */
    protected $identityMap;

    /** @var bool строго проверять значение первичного ключа */
    protected $strictPrimary = true;

    /**
     * Установка строгой проверки значения первичного ключа.
     * @return self
     */
    public function strictPrimary()
    {
        $this->strictPrimary = true;
        return $this;
    }

    /**
     * Установка не строгой проверки значения первичного ключа.
     * @return self
     */
    public function notStrictPrimary()
    {
        $this->strictPrimary = false;
        return $this;
    }

    /**
     * Проверка строгости проверки значения первичного ключа.
     * @return bool
     */
    public function isStrictPrimary(): bool
    {
        return $this->strictPrimary;
    }

    /**
     * Получение маппинга идентичности сущностей.
     * @return IdentityMap
     */
    public function identityMap(): IdentityMap
    {
        if (empty($this->identityMap)) {
            $this->identityMap = new IdentityMap($this);
        }
        return $this->identityMap;
    }

    /**
     * Обновление записи в IdentityMap и возвращение объекта.
     * @param object
     * @param string первичный ключ
     * @return object
     */
    public function identityMapUpdate(object &$object, string $primaryKey): object
    {
        return $this->identityMap()->update($object, $primaryKey);
    }

    /**
     * Получение состояния объекта из IdentityMap.
     * @param object
     * @param string первичный ключ
     * @return array|null
     */
    public function identityMapGetState(object &$object, string $primaryKey): ?array
    {
        return $this->identityMap()->getState($object, $primaryKey);
    }

    /**
     * Удаление записи из IdentityMap.
     * @param object
     * @param string первичный ключ
     */
    public function identityMapUnset(object &$object, string $primaryKey)
    {
        $this->identityMap()->unset($object, $primaryKey);
    }

    /**
     * Очистка IdentityMap.
     */
    public function identityMapClear()
    {
        unset($this->identityMap);
    }
}
