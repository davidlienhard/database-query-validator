<?php declare(strict_types=1);

/**
 * contains Filelist interface
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator\Filelist;

interface FilelistInterface
{
    /**
     * creates a new filelist
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array<\SplFileinfo>     $filelist       list of files to scan
     */
    public function __construct(array $filelist = []);

    /**
     * adds a new file to the list
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           \SplFileinfo    $file       file to add to the list
     */
    public function addFile(\SplFileinfo $file) : void;

    /**
     * returns all files in the list
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getFiles() : array;

    /**
     * returns the number of files in the list
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getFilecount() : int;
}
