<?php
/**
 * contains Filter Interface
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Scanner\Filter;

/**
 * contains Filter Interface
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
interface FilterInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           \RecursiveIterator  $iterator       iterator to use
     * @param           string              $absoluteFolder absolute folder of the root directory of the project to scan
     * @param           array               $exclusions     list of exclusions
     */
    public function __construct(\RecursiveIterator $iterator, string $absoluteFolder, array $exclusions);

    /**
     * checks whether or not to accept the current file/folder
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function accept() : bool;

    /**
     * fetches the children of the current iterator
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getChildren() : self;
}
