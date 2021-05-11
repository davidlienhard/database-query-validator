<?php
/**
 * contains StdinScanner class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Scanner;

use DavidLienhard\Database\QueryValidator\Scanner\ScannerInterface;
use DavidLienhard\Database\QueryValidator\Tester\TesterInterface;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;

/**
 * class to scan a folder a start the tests on the files
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class StdinScanner implements ScannerInterface
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
        $handle = fopen('php://stdin', 'r');
        if ($handle === false) {
            throw new \Exception("unable to open stdin handle");
        }

        stream_set_blocking($handle, true);
        $fileContents = stream_get_contents($handle);
        fclose($handle);

        if ($fileContents === false) {
            throw new \Exception("unable to read data from stdin");
        }

        $files = explode("\n", $fileContents);

        foreach ($files as $filename) {
            if (strtolower(substr($filename, -4, 4)) !== ".php") {
                continue;
            }

            foreach ($exclusions as $exclusion) {
                $absolutePath = Path::makeAbsolute($filename, $absoluteFolder);
                $absoluteExclusion = Path::makeAbsolute($exclusion, $absoluteFolder);
                if (Glob::match($absolutePath, $absoluteExclusion)) {
                    continue(2);
                }
            }

            $this->tester->test($filename);
        }
    }
}
