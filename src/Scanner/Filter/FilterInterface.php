<?php declare(strict_types=1);

/**
 * contains Filter Interface
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator\Scanner\Filter;

/**
 * contains Filter Interface
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
interface FilterInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           \RecursiveIterator<int, \RecursiveDirectoryIterator>    $iterator       iterator to use
     * @param           string              $absoluteFolder absolute folder of the root directory of the project to scan
     * @param           array               $exclusions     list of exclusions
     */
    public function __construct(\RecursiveIterator $iterator, string $absoluteFolder, array $exclusions);

    /**
     * checks whether or not to accept the current file/folder
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function accept() : bool;

    /**
     * fetches the children of the current iterator
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getChildren() : self;
}
