<?php
/**
 * contains Query class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Queries;

use DavidLienhard\Database\QueryValidator\Queries\QueryInterface;

/**
 * object that will hold a database-query
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class Query implements QueryInterface
{
    /**
     * creates the query-object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $query          string representation of the query
     * @param           array<string>   $parameters     list of parameters passed to the query
     * @param           string          $filename       name of the file containing the query
     * @param           int             $linenumber     linenumber in the file where the query begins
     */
    public function __construct(
        private string $query,
        private array $parameters,
        private string $filename,
        private int $linenumber
    ) {
    }

    /**
     * returns the query as a string
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getQuery() : string
    {
        return $this->query;
    }

    /**
     * returns the parameters as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * returns the filename as a string
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getFilename() : string
    {
        return $this->filename;
    }

    /**
     * returns the linenumber as a string
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getLinenumber() : int
    {
        return $this->linenumber;
    }

    /**
     * returns whether this query is prepared or not
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function isPrepared() : bool
    {
        return count($this->parameters) > 0;
    }
}
