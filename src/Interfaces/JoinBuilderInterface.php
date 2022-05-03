<?php
/**
 * Интерфейс сборщика JOIN части запросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Interfaces;

use Evas\Db\Interfaces\JoinBuilderInterface;
use Evas\Db\Interfaces\QueryBuilderInterface;

interface JoinBuilderInterface
{
    // /**
    //  * Конструктор.
    //  * @param QueryBuilderInterface
    //  * @param string|null тип склейки INNER | LEFT | RIGHT | OUTER
    //  * @param string|null таблица склейки
    //  */
    // public function __construct(QueryBuilderInterface $queryBuilder, string $type = null, string $tbl = null);

    // /**
    //  * Установка склеиваемой таблицы.
    //  * @param string склеиваемая таблица или запрос записей склеиваемой таблицы
    //  * @param array|null значения для экранирования\
    //  * @return self
    //  */
    // public function from(string $from, array $values = []): JoinBuilderInterface;

    // /**
    //  * Установка псевдонима для склеиваемой таблицы.
    //  * @param string псевдоним
    //  * @return self
    //  */
    // public function as(string $as): JoinBuilderInterface;

    // /**
    //  * Установка условия склеивания.
    //  * @param string условие
    //  * @param string значения для экранирования
    //  * @return QueryBuilderInterface
    //  */
    // public function on(string $on, array $values = []): QueryBuilderInterface;

    // /**
    //  * Установка столбца связывания.
    //  * @param string столбец
    //  * @return QueryBuilderInterface
    //  */
    // public function using(string $column): QueryBuilderInterface;

    // /**
    //  * Получение sql.
    //  * @return string
    //  */
    // public function getSql(): string;

    // /**
    //  * Сборка join-части запроса и его установка в сборщик запроса.
    //  * @return QueryBuilderInterface
    //  */
    // public function endJoin(): QueryBuilderInterface;
}
