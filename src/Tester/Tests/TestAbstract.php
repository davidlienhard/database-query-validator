<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester\Tests;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Queries\QueryInterface;

abstract class TestAbstract implements TestInterface
{
    /**
     * list of errors that occurred validating the query
     * array<string>
     */
    protected array $errors = [];

    /**
     * sets dependencies
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           QueryInterface  $query          query to validate
     * @param           DumpData        $dumpData       data from the database-dump
     * @param           array           $options        optional options to pass to the test
     */
    public function __construct(
        protected QueryInterface $query,
        protected DumpData $dumpData,
        protected array $options = []
    ) {
    }

    /**
     * starts the validation
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    abstract public function validate() : bool;

    /**
     * returns the number of errors
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrorcount() : int
    {
        return count($this->errors);
    }

    /**
     * returns the list of errors
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @return          array<string>
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
