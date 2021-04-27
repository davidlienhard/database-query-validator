<?php
/**
 * containers Query interface
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Queries;

/**
 * describes an object that will hold a database-query
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
interface QueryInterface
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
        string $query,
        array $parameters,
        string $filename,
        int $linenumber
    );

    /**
     * returns the query as a string
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getQuery() : string;

    /**
     * returns the parameters as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getParameters() : array;

    /**
     * returns the filename as a string
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getFilename() : string;

    /**
     * returns the linenumber as a string
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getLinenumber() : int;

    /**
     * returns whether this query is prepared or not
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function isPrepared() : bool;

    /**
     * returns the type of this query
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getType() : int;
}
