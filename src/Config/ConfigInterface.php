<?php
/**
 * contains Config Interface class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Config;

/**
 * object that contains configuration data for this validator
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
interface ConfigInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $config         configuration data to add
     */
    public function __construct(array $config);

    /**
     * gets a configuration entry from the object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $keys           keys to find the config entry
     */
    public function get(string ...$keys) : mixed;
}
