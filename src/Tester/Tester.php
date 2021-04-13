<?php
/**
 * containers Tester class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Output\OutputInterface;
use DavidLienhard\Database\QueryValidator\Tester\TesterInterface;

/**
 * class to test files
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
class Tester implements TesterInterface
{
    /**
     * list of errors that occurred
     * @var     string[]
     */
    private array $errors = [];

    /**
     * list of files that have been scanned
     * @var     string[]
     */
    private array $scannedFiles = [];

    /** number of query that have been checked */
    private int $queryCount = 0;

    /**
     * sets dependencies
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           OutputInterface $output         output object to use
     * @param           DumpData        $dumpData       data from the database-dump
     */
    public function __construct(private OutputInterface $output, private DumpData $dumpData)
    {
    }

    /**
     * tests one file
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           file to scan
     */
    public function test(string $file) : void
    {
        try {
            $testFile = new TestFile($file, $this->output, $this->dumpData);
            $testFile->validate();

            $this->addErrors($testFile->getErrors());
            $this->addQueryCount($testFile->getQueryCount());

            $this->scannedFiles[] = $file;
        } catch (\Exception $e) {
            throw new \Exception(
                "unable to validate file '".$file."'",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * adds an error to the internal error list
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string              $file       file where the error occured
     * @param           int                 $line       linenumber of the beginning of the query
     * @param           string              $error      error description
     */
    private function addError(string $file, int $line, string $error): void
    {
        $this->errors[] = "error in file '".$file.":".$line."' ".$error;
    }

    /**
     * adds multiple errors to the liost
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           array               $errors     errors to add
     */
    private function addErrors(array $errors) : void
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * adds multiple scanned files to the liost
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           array               $scannedFiles   files to add
     */
    private function addScannedFiles(array $scannedFiles) : void
    {
        $this->scannedFiles = array_merge(
            $this->scannedFiles,
            $scannedFiles
        );
    }

    /**
     * adds querycount to current count
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           int                 $queryCount     query count to add
     */
    private function addQueryCount(int $queryCount) : void
    {
        $this->queryCount += $queryCount;
    }

    /**
     * returns the number of errors
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrorcount() : int
    {
        return count($this->errors);
    }

    /**
     * returns the number of scanned files
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getFilecount() : int
    {
        return count($this->scannedFiles);
    }

    /**
     * returns the list of scanned files
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getScannedFiles() : array
    {
        return $this->scannedFiles;
    }

    /**
     * returns the number of queries scanned
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getQuerycount() : int
    {
        return $this->queryCount;
    }

    /**
     * returns the list of errors
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
