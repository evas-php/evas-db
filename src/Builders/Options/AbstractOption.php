<?php
namespace Evas\Db\Builders\Options;

abstract class AbstractOption
{
    public $type;
    
    /**
     * Конструктор.
     * @param string тип
     * @param array|null свойства
     */
    protected function __construct(string $type, array $props = null)
    {
        if ($props) foreach ($props as $name => $value) {
            $this->$name = $value;
        }
        $this->type = $type;
    }
}
