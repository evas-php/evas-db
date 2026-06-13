<?php
/**
 * Трейт тарнзакций базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Interfaces\DatabaseInterface;

trait DatabaseTransactionsTrait
{
    /**
     * Проверка открытости транзакции.
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->getPdo()->inTransaction();
    }

    /**
     * Создание транзакции.
     * @return self
     */
    public function beginTransaction(): DatabaseInterface
    {
        $this->rollBack()->getPdo()->beginTransaction();
        return $this;
    }

    /**
     * Отмена транзакции.
     * @return self
     */
    public function rollBack(): DatabaseInterface
    {
        if (true === $this->inTransaction()) $this->getPdo()->rollBack();
        return $this;
    }

    /**
     * Коммит транзакции.
     * @return self
     */
    public function commit(): DatabaseInterface
    {
        if (true === $this->inTransaction()) $this->getPdo()->commit();
        return $this;
    }

    /**
     * Выполнение функции в транзакции с коммитом в конце.
     * @param \Closure колбек-функция для выполнения внутри транзакции
     * @return self
     */
    public function transaction(\Closure $callback): DatabaseInterface
    {
        $this->beginTransaction();
        $callback = $callback->bindTo($this);
        $callback();
        return $this->commit();
    }
}
