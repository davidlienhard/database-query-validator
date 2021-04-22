<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester\Tests;

use PhpMyAdmin\SqlParser\Lexer as SqlLexer;
use PhpMyAdmin\SqlParser\Parser as SqlParser;
use PhpMyAdmin\SqlParser\Utils\Error as SqlParserError;

class Syntax extends TestAbstract
{
    /**
     * starts the validation
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function validate() : bool
    {
        $query = $this->query->getQuery();
        $query = trim($query, " \t\n\r\0\x0B\"");

        // replace \n (as string) with newlines and questionmarks with 1 for validation
        $from = [ "/\\\\n/", "/\?/" ];
        $to = [ "\n", 1 ];
        $query = \preg_replace($from, $to, $query);

        if ($query === null) {
            return false;
        }

        $lexer = new SqlLexer($query, false);
        $parser = new SqlParser($lexer->list);
        $errors = SqlParserError::get([$lexer, $parser]);

        if (count($errors) === 0) {
            return true;
        }

        $this->errors [] = SqlParserError::format($errors);

        return false;
    }
}
