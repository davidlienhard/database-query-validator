<?php
/**
 * contains Scanner Interface
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Scanner;

use \DavidLienhard\Database\QueryValidator\Tester\TesterInterface;

/**
 * describes a scanner class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
interface ScannerInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           TesterInterface $tester         tester object to use
     */
    public function __construct(TesterInterface $tester);

    /**
     * starts the scan
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $paths          list of paths to scan
     * @param           string          $absoluteFolder absolute path to the root of the project to scan
     * @param           array           $exclusions     list of exclusions
     */
    public function scan(array $paths, string $absoluteFolder, array $exclusions) : void;
}
