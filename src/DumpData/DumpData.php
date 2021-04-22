<?php
/**
 * contains DumpData class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\DumpData;

/**
 * contains data from the databasedump in structured form
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class DumpData
{
    /**
     * list of columns with tablename
     * @var     array
     */
    private array $withTable = [];

    /**
     * list of columns without tablename
     * @var     array
     */
    private array $withoutTable = [];

    /**
     * creates a new Dump object
     * expects the dump-data as an associative array with the keys
     * `tableName`, `columnName` & `dataType`
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $dumpData       data from the dump
     */
    public function __construct(array $dumpData = [])
    {
        foreach ($dumpData as $column) {
            $tableName = $column['tableName'];
            $columnName = $column['columnName'];
            $dataType = $column['dataType'];

            $this->withTable[$tableName][$columnName] = $dataType;
            $this->withoutTable[$columnName] = $dataType;
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
    public function getWithTable(string $tableName, string $columnName) : string|null
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
    public function getWithoutTable(string $columnName) : string|null
    {
        return $this->withoutTable[$columnName] ?? null;
    }
}
