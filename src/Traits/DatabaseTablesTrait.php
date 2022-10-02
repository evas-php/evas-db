<?php
/**
 * Трейт таблиц базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Table;

trait DatabaseTablesTrait
{
    /** @var array список таблиц */
    protected $tablesList;
    /** @var array маппинг объектов таблиц */
    protected $tableObjects = [];

    /**
     * Получение списка таблиц базы данных.
     * @param bool перезапросить список таблиц
     * @return array
     */
    public function tablesList(bool $reload = false): array
    {
        if (null === $this->tablesList || true === $reload) {
            $this->tablesList = $this->schemaCache()->tablesList($reload);
        }
        return $this->tablesList;
    }

    /**
     * Получение объекта таблицы.
     * @param string имя таблицы
     * @return Table
     */
    public function table(string $table): Table
    {
        $table = $this->grammar()->unwrap($table);
        if (empty($this->tableObjects[$table])) {
            $this->tableObjects[$table] = new Table($this, $table);
        }
        return $this->tableObjects[$table];
    }
}
