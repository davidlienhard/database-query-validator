<?php
/**
 * contains Filter class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Scanner\Filter;

use \Webmozart\Glob\Glob;
use \Webmozart\PathUtil\Path;

/**
 * class to filter while scanning folders
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
class Filter extends \RecursiveFilterIterator implements FilterInterface
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
    public function __construct(\RecursiveIterator $iterator, private string $absoluteFolder, private array $exclusions)
    {
        parent::__construct($iterator);
    }

    /**
     * checks whether or not to accept the current file/folder
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function accept() : bool
    {
        $filename = $this->current()->getFilename();
        $pathname = $this->current()->getPathname();

        if ($filename[0] === '.') {
            return false;
        }

        if ($this->current()->isDir()) {
            return true;
        }

        if (strtolower(substr($filename, -4, 4)) !== ".php") {
            return false;
        }

        foreach ($this->exclusions as $exclusion) {
            $absolutePath = Path::makeAbsolute($pathname, $this->absoluteFolder);
            $absoluteExclusion = Path::makeAbsolute($exclusion, $this->absoluteFolder);
            if (Glob::match($absolutePath, $absoluteExclusion)) {
                return false;
            }
        }

        return true;
    }

    /**
     * fetches the children of the current iterator
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getChildren() : self
    {
        return new self(
            $this->getInnerIterator()->getChildren(),
            $this->absoluteFolder,
            $this->exclusions
        );
    }
}
