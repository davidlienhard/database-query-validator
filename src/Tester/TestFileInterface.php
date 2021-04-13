<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Output\OutputInterface;

interface TestFileInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           path to the file to validate
     * @param           OutputInterface $output         output object to use
     * @param           DumpData        $dumpData       data from the database-dump
     */
    public function __construct(string $file, OutputInterface $output, DumpData $dumpData);

    /**
     * starts the validation
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function validate() : void;

    /**
     * validates a list of queries
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string              $file       the file containing the queries
     * @param           array               $queries    the queries to validate
     * @param           bool                $hasUseStmt whether this file has the use statement
     * @throws          \Exception                      if the file does not exist
     */
    public function validateQueries(string $file, array $queries, bool $hasUseStmt) : void;

    /**
     * returns the number of errors
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrorcount() : int;

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
