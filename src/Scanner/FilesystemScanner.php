<?php declare(strict_types=1);

/**
 * contains FilesystemScanner class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator\Scanner;

use DavidLienhard\Database\QueryValidator\Scanner\Filter\Filter;
use DavidLienhard\Database\QueryValidator\Scanner\ScannerInterface;
use DavidLienhard\Database\QueryValidator\Tester\TesterInterface;

/**
 * class to scan a folder a start the tests on the files
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class FilesystemScanner implements ScannerInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           TesterInterface $tester         tester object to use
     */
    public function __construct(private TesterInterface $tester)
    {
    }

    /**
     * starts the scan
     *
     * @author          David Lienhard <github@lienhard.win>
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
                if (!($info instanceof \SplFileInfo)) {
                    continue;
                }

                $this->tester->test($info->getPathname());
            }
        }
    }
}
