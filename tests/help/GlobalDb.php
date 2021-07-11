<?php
/**
 * Хелпер unit-тестов модуля evas-db.
 * @package evas-php/evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\tests\help;

use Evas\Db\Database;

class GlobalDb
{
    const CONFIG_PATH = __DIR__ . '/../_config/db_tests_config.php';
    public static function config()
    {
        static $config = null;
        if (null === $config) {
            $config = include static::CONFIG_PATH;
        }
        return $config;
    }

    public static function staticDb(): Database
    {
        static $db = null;
        if (null === $db) {
            codecept_debug('Make staticDb');
            $config = static::config();
            $db = new Database($config);
            $db->batchQuery('
                DROP TABLE IF EXISTS `auths`;
                DROP TABLE IF EXISTS `users`;
                CREATE TABLE IF NOT EXISTS `users` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `name` varchar(60) NOT NULL,
                  `email` varchar(60) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                CREATE TABLE IF NOT EXISTS `auths` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `user_id` int(10) UNSIGNED NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                ALTER TABLE `auths` ADD KEY `user_id` (`user_id`);
                ALTER TABLE `auths`
                    ADD CONSTRAINT `auths_to_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
            ');
        }
        return $db;
    }
}
