<?php
/**
 * contains Outout interface
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Output;

use \DavidLienhard\Database\QueryValidator\Tester\TesterInterface;

/**
 * interface to output results of the validation
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
interface OutputInterface
{
    /**
     * outputs the result of a single query
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $filename       name if the file containing the query
     * @param           int             $line           number of the line
     * @param           bool            $result         result of the validation
     */
    public function query(string $filename, int $line, bool $result) : void;

    /**
     * outputs an error
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $error          error to output
     * @param           int             $errorCode      code of the error
     */
    public function error(string $error, int $errorCode = 1) : void;

    /**
     * outputs the summary at the end of the validation
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           TesterInterface $tester         tester object containing all the results
     */
    public function summary(TesterInterface $tester) : void;
}
