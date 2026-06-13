<?php
/**
 * Сборщик JOIN части запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Db\Builders\AbstractQueryBuilder;
// use Evas\Db\Interfaces\JoinBuilderInterface;
use Evas\Db\Interfaces\DatabaseInterface;

class JoinBuilder extends AbstractQueryBuilder// implements JoinBuilderInterface
{
    /** @static array поддерживаемые типы джоинов */
    protected static $types = [
        '', 'INNER', 'LEFT', 'RIGHT',
        'LEFT OUTER', 'RIGHT OUTER',
    ];

    /** @var array доступные операторы */
    public static $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
    ];

    /** @var DatabaseInterface соединение с базой данных */
    protected $db;

    /** @var string тип склейки */
    public $type;

    /** @var array условие склеивания */
    public $on = [];

    /** @var string столбец склеивания */
    public $using;

    /**
     * Конструктор.
     * @param DatabaseInterface
     * @param string|null тип склейки INNER | LEFT | RIGHT | LEFT OUTER | RIGHT OUTER
     * @param string|null таблица склейки
     * @throws InvalidArgumentException
     */
    public function __construct(
        DatabaseInterface $db, string $type = null, $tbl = null, string $as = null
    ) {
        $type = strtoupper(trim($type));
        if (!in_array($type, static::$types)) {
            throw new \InvalidArgumentException(sprintf('Not supported JOIN type: %s', $type));
        }
        $this->type = $type;
        $this->db = $db;
        if ($tbl) $this->from($tbl, $as);
    }

    /**
     * Добавление On условия в сборку.
     * @param string тип сборки
     * @param array данные условия
     * @return self
     */
    protected function pushOn(string $type, array $on)
    {
        $on['type'] = $type;
        $this->on[] = $on;
        if (!empty($on['bindings'])) {
            $this->addBindings('wheres', $on['bindings']);
        }
        return $this;
    }

    protected function pushSingleOn(
        bool $isOr, string $first, string $operator, string $second = null
    ) {
        $this->prepareValueAndOperator($second, $operator, !$second);
        return $this->pushOn('SingleColumn', compact('first', 'operator', 'second', 'isOr'));
    }

    /**
     * Установка условия склеивания sql-строкой.
     * @param string sql-условие
     * @param array|null значения для экранирования
     * @param bool|null использовать ли OR для склейки
     * @return self
     */
    public function onRaw(string $sql, array $bindings = [], bool $isOr = false)
    {
        return $this->pushOn('Raw', compact('sql', 'bindings', 'isOr'));
    }

    /**
     * Установка условия склеивания sql-строкой через OR.
     * @param string sql-условие
     * @param array|null значения для экранирования
     * @return self
     */
    public function orOnRaw(string $sql, array $bindings = [])
    {
        return $this->onRaw($sql, $bindings, true);
    }

    /**
     * Установка условия склеивания.
     * @param string столбец первой таблицы
     * @param string оператор|столбец второй таблицы
     * @param string|null столбец второй таблицы или null
     * @return self
     */
    public function on(string $first, string $operator, string $second = null)
    {
        return $this->pushSingleOn(false, ...func_get_args());
    }

    /**
     * Установка условия склеивания через OR.
     * @param string условие
     * @param string значения для экранирования
     * @return self
     */
    public function orOn(string $first, string $operator, string $second = null)
    {
        return $this->pushSingleOn(true, ...func_get_args());
    }

    /**
     * Установка столбца связывания.
     * @param string столбец
     * @return self
     */
    public function using(string $column)
    {
        $this->using = $this->wrap($column);
        return $this;
    }

    /**
     * Получение sql-запроса.
     * @return string
     */
    public function getSql(): string
    {
        return $this->db->grammar()->buildJoin($this);
    }

    /**
     * Получение всех экранируемых значений.
     * @return array
     */
    public function getBindingsAll(): array
    {
        return $this->getBindings(['from', 'on']);
    }
}
