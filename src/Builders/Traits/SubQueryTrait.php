<?php
/**
 * Трейт сборки подзапросов.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Builders\Traits;

use Evas\Db\Interfaces\QueryBuilderInterface;

trait SubQueryTrait
{
    /**
     * Создание нового экземпляра сборщика с тем же соединением.
     * @return QueryBuilderInterface
     */
    protected function newQuery(): QueryBuilderInterface
    {
        return $this->db->buildQuery();
    }

    /**
     * Создание подзапроса с получением sql и экранируемых значений.
     * @param \Closure|self
     * @return array [sql, bindings]
     * @throws \InvalidArgumentException
     */
    protected function createSub($query): array
    {
        if ($query instanceof \Closure) {
            // $cb = $query;
            // $cb($query = $this->forSubQuery());
            call_user_func($query, $query = $this->newQuery());
        }

        if ($query instanceof QueryBuilderInterface) {
            $query = $this->setDbNameIfCrossDatabaseQuery($query);
            return ['(' . $query->getSql() . ')', $query->getBindings()];
        }
        else if (is_string($query)) {
            $query = preg_match('/^\w+(\.\w+)?$/u', $query) 
            ? $this->wrap($query) : "({$query})";
            return [$query, []];
        }
        else {
            throw new \InvalidArgumentException(sprintf(
                'A subquery must be an instance of %s, a Closure, or a string, %s given',
                QueryBuilderInterface::class,
                PhpHelp::getType($query)
            ));
        }
    }

    /**
     * Смена имени базы для подзапроса к другой базе.
     * @param QueryBuilderInterface
     * @return QueryBuilderInterface
     */
    protected function setDbNameIfCrossDatabaseQuery(QueryBuilderInterface $query)
    {
        $dbname = $query->db->dbname;
        if ($dbname !== $this->db->dbname) {
            if (strpos($query->from, '.') === false) {
                $query->resetFrom()->from($this->wrap($dbname.'.'.$query->from));
            }
        }
        return $query;
    }
}
