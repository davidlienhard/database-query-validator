<?php declare(strict_types=1);

/**
 * contains DumpData class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator\DumpData;

use DavidLienhard\Database\QueryValidator\DumpData\ColumnInterface;

/**
 * contains data from the databasedump in structured form
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class DumpData
{
    /** list of columns with tablename */
    private array $withTable = [];

    /** list of columns without tablename */
    private array $withoutTable = [];

    /**
     * creates a new Dump object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array<\DavidLienhard\Database\QueryValidator\DumpData\ColumnInterface>   $columns    list of colums to add
     */
    public function __construct(array $columns = [])
    {
        foreach ($columns as $column) {
            if (!($column instanceof ColumnInterface)) {
                throw new \InvalidArgumentException("colums must be type of ColumnInterface");
            }

            $this->withTable[$column->getTable()][$column->getName()] = $column;
            $this->withoutTable[$column->getName()] = $column;
        }
    }

    /**
     * returns the data-type of a column with a specific table
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $tableName      name of the table
     * @param           string          $columnName     name of the column
     */
    public function getWithTable(string $tableName, string $columnName) : ColumnInterface|null
    {
        return $this->withTable[$tableName][$columnName] ?? null;
    }

    /**
     * returns the data-type of a column without a known table
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $columnName     name of the column
     */
    public function getWithoutTable(string $columnName) : ColumnInterface|null
    {
        return $this->withoutTable[$columnName] ?? null;
    }

    /**
     * returns the data-type of a column without a known table
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $tableName      name of the table
     * @return          array<\DavidLienhard\Database\QueryValidator\DumpData\ColumnInterface>|null
     */
    public function getColumsForTable(string $tableName) : array|null
    {
        return $this->withTable[$tableName] ?? null;
    }
}
