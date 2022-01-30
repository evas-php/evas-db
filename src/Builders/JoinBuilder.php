<?php
/**
 * Сборщик JOIN части запроса.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders;

use Evas\Db\Builders\Traits\ForQueryAndJoinBuildersTrait;
use Evas\Db\Builders\QueryBuilder;
use Evas\Db\Builders\QueryValuesTrait;
use Evas\Db\Interfaces\JoinBuilderInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;

class JoinBuilder implements JoinBuilderInterface
{
    /** Подключаем вспомогательные методы для сборщика */
    use ForQueryAndJoinBuildersTrait;

    /** @static array поддерживаемые типы джоинов */
    protected static $types = [
        '', 'INNER', 'LEFT', 'RIGHT',
        'LEFT OUTER', 'RIGHT OUTER',
    ];

    /** @var array доступные операторы */
    public static $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
    ];

    /** @var QueryBuilder */
    protected $queryBuilder;

    /** @var string тип склейки */
    public $type;

    /** @var string склеиваемая таблица или запрос записей склеиваемой таблицы */
    public $from;

    /** @var string псевдоним склеиваемой таблицы */
    public $as;

    /** @var array условие склеивания */
    public $on = [];

    /** @var array значения для экранирования */
    public $bindings = [
        'on' => [],
    ];

    /** @var string столбец склеивания */
    public $using;

    /**
     * Конструктор.
     * @param QueryBuilderInterface
     * @param string|null тип склейки INNER | LEFT | RIGHT | LEFT OUTER | RIGHT OUTER
     * @param string|null таблица склейки
     * @throws InvalidArgumentException
     */
    public function __construct(QueryBuilderInterface $queryBuilder, string $type = null, string $tbl = null)
    {
        $type = strtoupper(trim($type));
        if (!in_array($type, static::$types)) {
            throw new \InvalidArgumentException(sprintf('Not supported JOIN type: %s', $type));
        }
        $this->queryBuilder = $queryBuilder;
        $this->type = $type;
        $this->from = $tbl;
    }

    /**
     * Установка псевдонима для склеиваемой таблицы.
     * @param string псевдоним
     * @return self
     */
    public function as(string $as): JoinBuilderInterface
    {
        $this->as = $as;
        return $this;
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
        if (!empty($on['values'])) {
            $this->addBindings('on', $on['values']);
        } else if (isset($on['value'])) {
            $this->addBinding('on', $on['value']);
        }
        return $this;
    }

    /**
     * Установка условия склеивания sql-строкой.
     * @param string sql-условие
     * @param string значения для экранирования
     * @return self
     */
    public function onRaw(string $sql, array $values = []): JoinBuilderInterface
    {
        return $this->pushOn('Raw', compact('sql', 'values'));
    }

    /**
     * Установка условия склеивания sql-строкой через OR.
     * @param string sql-условие
     * @param string значения для экранирования
     * @return self
     */
    public function orOnRaw(string $sql, array $values = []): JoinBuilderInterface
    {
        $isOr = true;
        return $this->pushOn('Raw', compact('sql', 'values', 'isOr'));
    }

    /**
     * Установка условия склеивания.
     * @param string столбец первой таблицы
     * @param string оператор|столбец второй таблицы
     * @param string|null столбец второй таблицы или null
     * @return self
     */
    public function on(string $first, string $operator, string $second = null): JoinBuilderInterface
    {
        [$second, $operator] = $this->prepareValueAndOperator($second, $operator, !$second);
        return $this->pushOn('SingleColumn', compact('first', 'operator', 'second'));
    }

    /**
     * Установка условия склеивания через OR.
     * @param string условие
     * @param string значения для экранирования
     * @return self
     */
    public function orOn(string $first, string $operator, string $second = null): JoinBuilderInterface
    {
        $isOr = true;
        [$second, $operator] = $this->prepareValueAndOperator($second, $operator, !$second);
        return $this->pushOn('SingleColumn', compact('first', 'operator', 'second', 'isOr'));
    }

    /**
     * Установка столбца связывания.
     * @param string столбец
     * @return self
     */
    public function using(string $column): JoinBuilderInterface
    {
        $this->using = $column;
        return $this;
    }

    /**
     * Получение sql.
     * @return string
     */
    public function getSql(): string
    {
        return $this->queryBuilder->db->grammar()->buildJoin($this);
    }

    public function getBindings(): array
    {
        return $this->bindings['on'] ?? [];
    }

    public function getSqlAndBindings(): array
    {
        return [$this->getSql(), $this->getBindings()];
    }
}
