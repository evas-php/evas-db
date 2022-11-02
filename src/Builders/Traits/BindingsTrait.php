<?php
/**
 * Трейт установки экранируемых значений.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

trait BindingsTrait
{
    protected $bindings = [
        // 'from' => [],
        // 'update' => [],
        // 'columns' => [],
        // 'joins' => [],
        // 'wheres' => [],
        // 'havings' => [],
        // 'unions' => [],
    ];

    /**
     * Добавление экранируемого значения.
     * @param string для какой части запроса
     * @param array экранируемые значения.
     * @return self
     */
    public function addBindings(string $part, array $bindings = [])
    {
        $this->bindings[$part] = array_merge($this->bindings[$part] ?? [], $bindings);
        return $this;
    }

    /**
     * Получение экранируемых значений.
     * @param string|array|null для какой части запроса
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getBindings($part = null): array
    {
        if (null !== $part) {
            if (is_string($part)) $part = [$part];
            if (!is_array($part)) {
                throw new \InvalidArgumentException(sprintf(
                    'Argument #1 passed to %s must be a string, an array or null, %s given',
                    __METHOD__, gettype($part)
                ));
            }
            $values = [];
            foreach ($part as $sub) {
                $values = array_merge($values, $this->bindings[$sub] ?? []);
            }
            return $values;
        }
        return $this->getBindingsAll();
    }

    /**
     * Получение всех экранируемых значений.
     * @return array
     */
    public function getBindingsAll(): array
    {
        $parts = ['from', 'joins', 'wheres', 'havings', 'unions'];
        if ('update' === $this->type) array_unshift($parts, 'update');
        else if ('delete' !== $this->type) array_unshift($parts, 'columns');
        return $this->getBindings($parts);
    }
}
