<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester\Tests;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Queries\QueryInterface;

interface TestInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           QueryInterface  $query          query to validate
     * @param           DumpData        $dumpData       data from the database-dump
     */
    public function __construct(
        QueryInterface $query,
        DumpData $dumpData
    );

    /**
     * starts the validation
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function validate() : bool;

    /**
     * returns the number of errors
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrorcount() : int;

    /**
     * returns the list of errors
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrors() : array;
}
