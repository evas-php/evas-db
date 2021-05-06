<?php
namespace Evas\Db\Traits;

use \InvalidArgumentException

/**
 * Константы для трейта по умолчанию.
 */
if (!defined('EVAS_DB_MODELS_IN_RESULT')) define('EVAS_DB_MODELS_IN_RESULT', true);


trait DatabaseModelsToTablesMapTrait
{
    /** @var bool используется ли возврат результата запроса в виде соответствующей модели */
    public $isModelsInResultUsed = EVAS_DB_MODELS_IN_RESULT;

    /** @var array маппинг моделей по таблицам */
    protected $map = [];

    /**
     * Используется ли возврат результата запроса в виде соответствующей модели.
     * @return bool
     */
    public function isModelsInResultUsed(): bool
    {
        return $this->isModelsToTablesMapUsed() && $this->isModelsInResultUsed;
    }

    public function isModelsToTablesMapUsed(): bool
    {
        return $this->isModelsToTablesMapUsed;
    }

    protected function loadMap(string $path)
    {
        // 
    }

    /**
     * Поиск модели таблицы.
     * @param string имя таблицы
     * @return string|null
     */
    public function findTableModel(string $table): ?string
    {
        return $this->map[$table] ?? null;
    }

    /**
     * Поиск таблицы модели.
     * @param string|object имя класса или объект модели
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function findModelTable($model): ?string
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        if (!is_string($model)) {
            $type = gettype($model);
            throw new InvalidArgumentException(
                "Argument 1 \$model must be a string or an object, $type given"
            );
        }
        return array_search($model, $this->map) ?? null;
    }
}
