<?php
/**
 * contains FromMysqlDump class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\DumpData;

use \DavidLienhard\Database\QueryValidator\DumpData\Column;
use \DavidLienhard\Database\QueryValidator\DumpData\DumpData;

/**
 * class to create a DumpData object from a mysql dump
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class FromMysqlDump
{
    /**
     * creates a new Dump object from a mysql-dump file
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $dumpFile       path to the dump-file
     */
    public static function getDumpData(string $dumpFile) : DumpData
    {
        if (!file_exists($dumpFile)) {
            throw new \Exception("dumpfile '".$dumpFile."' does not exist");
        }

        $fileContent = \file($dumpFile);
        if ($fileContent === false) {
            throw new \Exception("dumpfile '".$dumpFile."' does not exist");
        }

        $tableName = "";
        $dumpData = [];
        foreach ($fileContent as $line) {
            if (preg_match("/^CREATE TABLE `([A-z0-9\-\_]+)`/", $line, $matches)) {
                $tableName = $matches[1];
            }

            if (preg_match("/^  `([A-z0-9\-\_]+)` ([a-z]+)( |\()(NOT NULL|)/", $line, $matches)) {
                $dumpData[] = new Column(
                    table: $tableName,
                    name: $matches[1],
                    type: self::convertType($matches[2]),
                    isNull: $matches[4] !== "NOT NULL",
                    isText: $matches[2] === "text"
                );
            }
        }//end foreach

        return new DumpData($dumpData);
    }

    /**
     * converts mysql types to the ones required for prepared statements
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string              $type       the type to convert
     */
    private static function convertType(string $type) : string
    {
        $conversion = [
            "int"       => "i",
            "tinyint"   => "i",
            "smallint"  => "i",
            "mediumint" => "i",
            "float"     => "d",
            "double"    => "d",
            "varchar"   => "s",
            "char"      => "s",
            "text"      => "s",
            "enum"      => "s",
            "time"      => "s",
            "date"      => "s"
        ];

        return $conversion[strtolower($type)] ?? "-";
    }
}
