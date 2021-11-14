<?php declare(strict_types=1);

/**
 * contains Filter class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator\Scanner\Filter;

use DavidLienhard\Database\QueryValidator\Exceptions\QueryValidator as QueryValidatorException;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;

/**
 * class to filter while scanning folders
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class Filter extends \RecursiveFilterIterator implements FilterInterface
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
    public function __construct(\RecursiveIterator $iterator, private string $absoluteFolder, private array $exclusions)
    {
        parent::__construct($iterator);
    }

    /**
     * checks whether or not to accept the current file/folder
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function accept() : bool
    {
        $current = $this->current();
        if ((! $current instanceof \SplFileInfo) && (! $current instanceof \FilesystemIterator)) {
            $type = gettype($current) === "object" ? get_class($current) : gettype($current);
            throw new QueryValidatorException(
                "current element is of wrong type '".$type."'"
            );
        }

        $filename = $current->getFilename();
        $pathname = $current->getPathname();

        if ($filename[0] === '.') {
            return false;
        }

        if ($current->isDir()) {
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
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getChildren() : self
    {
        $inner = $this->getInnerIterator();

        if (! $inner instanceof \RecursiveArrayIterator) {
            throw new QueryValidatorException("inner iterator must be instance of 'RecursiveArrayIterator'");
        }

        return new self(
            $inner->getChildren(),
            $this->absoluteFolder,
            $this->exclusions
        );
    }
}
