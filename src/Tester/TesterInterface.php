<?php
/**
 * interface for tester class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester;

use \DavidLienhard\Database\QueryValidator\Output\OutputInterface;
use \DavidLienhard\Database\QueryValidator\DumpData\DumpData;

/**
 * interface for tester class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
interface TesterInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           OutputInterface $output         output object to use
     * @param           DumpData        $dumpData       data from the database-dump
     */
    public function __construct(OutputInterface $output, DumpData $dumpData);

    /**
     * tests one file
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           file to scan
     */
    public function test(string $file) : void;

    /**
     * returns the number of errors
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrorcount() : int;

    /**
     * returns the number of scanned files
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getFilecount() : int;

    /**
     * returns the list of scanned files
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getScannedFiles() : array;

    /**
     * returns the number of queries scanned
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getQuerycount() : int;

    /**
     * returns the list of errors
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrors() : array;
}
