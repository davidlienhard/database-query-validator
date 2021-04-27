<?php
/**
 * contains Standard Output class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Output;

use DavidLienhard\Database\QueryValidator\Output\OutputInterface;
use DavidLienhard\Database\QueryValidator\Tester\TesterInterface;

class Standard implements OutputInterface
{
    /** number of queries already printed */
    private int $queryCount = 0;

    /**
     * outputs the result of a single query
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $filename       name if the file containing the query
     * @param           int             $line           number of the line
     * @param           bool            $result         result of the validation
     */
    public function query(string $filename, int $line, bool $result) : void
    {
        echo $this->queryCount % 80 === 0 ? PHP_EOL : "";
        $this->queryCount++;

        echo $result ? "." : "x";
    }

    /**
     * outputs an error
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $error          error to output
     * @param           int             $errorCode      code of the error
     */
    public function error(string $error, int $errorCode = 1) : void
    {
        fwrite(STDERR, $error);
        exit($errorCode);
    }

    /**
     * outputs the summary at the end of the validation
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           TesterInterface $tester         tester object containing all the results
     */
    public function summary(TesterInterface $tester) : void
    {
        echo PHP_EOL."found ".$tester->getErrorcount()." errors ".
            "in ".$tester->getFilecount()." files ".
            "and ".$tester->getQuerycount()." queries".PHP_EOL.PHP_EOL;

        foreach ($tester->getErrors() as $error) {
            echo "- ".$error.PHP_EOL;
        }

        exit($tester->getErrorcount() === 0 ? 0 : 1);            // exit with code 0 (no errors) or 1 (errors)
    }
}
