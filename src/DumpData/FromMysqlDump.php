<?php
/**
 * contains FromMysqlDump class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\DumpData;

use DavidLienhard\Database\QueryValidator\DumpData\Column;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;

/**
 * class to create a DumpData object from a mysql dump
 *
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
    public static function getDumpData(Filesystem $filesystem, string $dumpFile) : DumpData
    {
        if (!$filesystem->fileExists($dumpFile)) {
            throw new \Exception("dumpfile '".$dumpFile."' does not exist");
        }

        try {
            $fileContent = $filesystem->read($dumpFile);
        } catch (FilesystemException | UnableToReadFile $e) {
            throw new \Exception("unable to read contents of file '".$dumpFile."'", $e->getCode(), $e);
        }

        $fileContent = explode("\n", $fileContent);

        $tableName = "";
        $dumpData = [];
        foreach ($fileContent as $line) {
            if (preg_match("/^CREATE TABLE `([A-z0-9\-\_]+)`/", $line, $matches)) {
                $tableName = $matches[1];
            }

            if (preg_match("/^  `([A-z0-9\-\_]+)` ([A-z]+)( |\()/", $line, $matches)) {
                $dumpData[] = new Column(
                    table: $tableName,
                    name: $matches[1],
                    type: self::convertType($matches[2]),
                    isNull: !str_contains(strtoupper($line), "NOT NULL"),
                    isText: strtolower($matches[2]) === "text"
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
