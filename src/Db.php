<?php
/**
 * Фасад соединений с базами данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db;

use Evas\Base\App;
use Evas\Base\Help\PhpHelp;
use Evas\Db\Database;
use Evas\Db\DatabasesManager;
use Evas\Db\Interfaces\DatabaseInterface;

class Db
{
    /** @static string имя модуля в di */
    const MODULE_NAME = 'db';

    /** @static DatabasesManager инстанс менеджера соединений */
    protected static $instance;

    /**
     * Получение иснтанса менеджера соединений.
     * @return DatabasesManager
     * @throws \InvalidArgumentException
     */
    protected static function instance(): DatabasesManager
    {
        if (!static::$instance) {
            if (!static::MODULE_NAME) {
                throw new \InvalidArgumentException(sprintf(
                    'Name for module %s must be exists', __CLASS__
                ));
            }
            if (!App::has(static::MODULE_NAME)) {
                throw new \InvalidArgumentException(sprintf(
                    'App DI not has entry %s', static::MODULE_NAME
                ));
            }
            $module = App::get(static::MODULE_NAME);
            if ($module instanceof DatabasesManager) {
                static::$instance = &$module;
            } else if ($module instanceof DatabaseInterface) {
                static::$instance = new DatabasesManager;
                static::$instance->set($module);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'App DI entry %s must be instance of %s or %s, %s given', 
                    static::MODULE_NAME, DatabasesManager::class, DatabaseInterface::class,
                    PhpHelp::getType($module),
                ));
            }
        }
        return static::$instance;
    }

    /**
     * Магический вызов метода.
     * @param string имя метода
     * @param array|null аргументы
     * @return mixed результат вызова метода
     */
    public function __call(string $name, array $args = null)
    {
        return static::__callStatic($name, $args);
    }

    /**
     * Магический статический вызов метода.
     * @param string имя метода
     * @param array|null аргументы
     * @return mixed результат вызова метода
     * @throws \BadMethodCallException
     */
    public static function __callStatic(string $name, array $args = null)
    {
        $instance = static::instance();
        if (method_exists($instance, $name)) {
            return $instance->$name(...$args);
        } else {
            $db = $instance->last();
            if (method_exists($db, $name)) return $db->$name(...$args);
        }
        throw new \BadMethodCallException(sprintf(
            'DatabasesManager and DatabaseInterface not has method %s',
            $name
        ));
    }
}
