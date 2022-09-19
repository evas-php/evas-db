<?php
/**
 * Грамматика PostreSQL.
 * @package evas-php\evas-db
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Db\Grammars;

use Evas\Db\Grammars\AbstractGrammar;
use Evas\Db\Interfaces\GrammarInterface;

class PgsqlGrammar extends AbstractGrammar implements GrammarInterface
{
    /**
     * Установка таймзоны.
     * @param string кодировка
     */
    public function setTimezone(string $timezone)
    {
        return $this->query("set time zone '{$timezone}'");
    }
}
