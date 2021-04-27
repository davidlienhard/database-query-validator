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
    public const TYPE_SELECT = 1;
    public const TYPE_INSERT = 2;
    public const TYPE_UPDATE = 4;
    public const TYPE_DELETE = 8;
    public const TYPE_CREATE = 16;
    public const TYPE_OPTIMIZE = 32;
    public const TYPE_UNKNOWN = 4096;

    /** type of this query (on of the TYPE_ constants) */
    private int $type = 0;

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
        // set type
        $arr = explode(" ", trim($query));
        $type = strtolower($arr[0]);

        $translation = [
            "select"   => self::TYPE_SELECT,
            "insert"   => self::TYPE_INSERT,
            "update"   => self::TYPE_UPDATE,
            "delete"   => self::TYPE_DELETE,
            "create"   => self::TYPE_CREATE,
            "optimize" => self::TYPE_OPTIMIZE
        ];

        $this->type = $translation[$type] ?? self::TYPE_UNKNOWN;
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

    /**
     * returns the type of this query
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getType() : int
    {
        return $this->type;
    }
}
