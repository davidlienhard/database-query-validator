<?php declare(strict_types=1);

/**
 * contains Output interface
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator\Output;

use DavidLienhard\Database\QueryValidator\Tester\TesterInterface;

/**
 * interface to output results of the validation
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
interface OutputInterface
{
    /**
     * outputs the result of a single query
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $filename       name if the file containing the query
     * @param           int             $line           number of the line
     * @param           bool            $result         result of the validation
     */
    public function query(string $filename, int $line, bool $result) : void;

    /**
     * outputs an error
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $error          error to output
     */
    public function error(string $error) : void;

    /**
     * outputs the summary at the end of the validation
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           TesterInterface $tester         tester object containing all the results
     */
    public function summary(TesterInterface $tester) : void;
}
