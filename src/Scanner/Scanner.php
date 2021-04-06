<?php
/**
 * contains Scanner class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Scanner;

use \DavidLienhard\Database\QueryValidator\Tester\TesterInterface;
use \DavidLienhard\Database\QueryValidator\Scanner\ScannerInterface;
use \DavidLienhard\Database\QueryValidator\Scanner\Filter\Filter;

/**
 * class to scan a folder a start the tests on the files
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
class Scanner implements ScannerInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           TesterInterface $tester         tester object to use
     */
    public function __construct(private TesterInterface $tester)
    {
    }

    /**
     * starts the scan
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $paths          list of paths to scan
     * @param           string          $absoluteFolder absolute path to the root of the project to scan
     * @param           array           $exclusions     list of exclusions
     */
    public function scan(array $paths, string $absoluteFolder, array $exclusions) : void
    {
        foreach ($paths as $path) {
            $directory = new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::FOLLOW_SYMLINKS
            );


            $filter = new Filter($directory, $absoluteFolder, $exclusions);

            $iterator = new \RecursiveIteratorIterator($filter);

            foreach ($iterator as $info) {
                $this->tester->test($info->getPathname());
            }
        }
    }
}
