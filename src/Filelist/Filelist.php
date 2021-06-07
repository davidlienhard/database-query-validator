<?php declare(strict_types=1);

/**
 * contains Filelist class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator\Filelist;

use DavidLienhard\Database\QueryValidator\Filelist\FilelistInterface;

class Filelist implements FilelistInterface
{
    /**
     * creates a new filelist
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array<\SplFileinfo>     $filelist       list of files to scan
     */
    public function __construct(private array $filelist = [])
    {
        foreach ($filelist as $file) {
            if (!($file instanceof \SplFileinfo)) {
                throw new \InvalidArgumentException("file must be instance of \SplFileinfo");
            }
        }
    }

    /**
     * adds a new file to the list
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           \SplFileinfo    $file       file to add to the list
     */
    public function addFile(\SplFileinfo $file) : void
    {
        $this->filelist[] = $file;
    }

    /**
     * returns all files in the list
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getFiles() : array
    {
        return $this->filelist;
    }

    /**
     * returns the number of files in the list
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getFilecount() : int
    {
        return count($this->filelist);
    }
}
