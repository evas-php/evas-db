<?php
/**
 * Трейт СУБД-грамматики базы данных.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Traits;

use Evas\Db\Interfaces\GrammarInterface;

if (!defined('EVAS_DB_GRAMMARS')) {
    define('EVAS_DB_GRAMMARS', [
        'mysql' => MysqlGrammar::class,
        'pgsql' => PgsqlGrammar::class,
    ]);
}
if (!defined('EVAS_DB_GRAMMAR_DEFAULT')) {
    define('EVAS_DB_GRAMMAR_DEFAULT', MysqlGrammar::class);
}

trait DatabaseGrammarTrait
{
    /** @static array маппинг СУБД-грамматик по драйверам */
    public static $grammars = EVAS_DB_GRAMMARS;

    /** @var GrammarInterface СУБД-грамматика соединения */
    protected $grammar;

    /**
     * Получение СУБД-грамматики соединения.
     * @return GrammarInterface
     */
    public function grammar(): GrammarInterface
    {
        if (!$this->grammar) {
            $grammar = static::$grammars[$this->driver] || throw new DatabaseQueryException(
                "Grammar for \"$this->driver\" driver doesn't exists"
            );
            $this->grammar = new $grammar($this);
        }
        return $this->grammar;
    }
}
