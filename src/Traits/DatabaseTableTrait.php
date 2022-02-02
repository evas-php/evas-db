<?php
/**
 * Трейт расширения базы данных подддержкой моделей таблиц.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Schema\TableSchema;
use Evas\Db\Table;

trait DatabaseTableTrait
{
    /** @var array список таблиц */
    protected $tablesList;
    /** @var array маппинг объектов таблиц */
    protected $tableObjects = [];

    /**
     * Получение объекта таблицы.
     * @param string имя таблицы
     * @return Table
     */
    public function table(string $table): Table
    {
        $table = $this->grammar()->unwrapTable($table);
        if (empty($this->tableObjects[$table])) {
            $this->tableObjects[$table] = new Table($this, $table);
        }
        return $this->tableObjects[$table];
    }

    /**
     * Получение объекта схемы таблицы.
     * @param string имя таблицы
     * @return TableSchema
     */
    public function tableSchema(string $table): TableSchema
    {
        if (empty($this->tableSchamas[$table])) {
            if (empty($this->tableObjects[$table])) {
                $this->tableSchamas[$table] = new TableSchema($this, $table);
            } else {
                $this->tableSchamas[$table] = &$this->tableObjects[$table];
            }
        }
        return $this->tableSchamas[$table];
    }
    
    /**
     * Получение списка таблиц базы данных.
     * @param bool перезапросить список таблиц
     * @return array
     */
    public function tablesList(bool $reload = false): ?array
    {
        if (null === $this->tablesList || true === $reload) {
            $this->tablesList = $this->grammar()->getTablesList();
        }
        return $this->tablesList;
    }

    /**
     * Получение максимального id записи.
     * @param string имя таблицы
     * @return int
     */
    public function maxId(string $table): int
    {
        return $this->table($table)->getMax('id');
    }
}
