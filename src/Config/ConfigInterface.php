<?php
/**
 * contains Config Interface class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Config;

/**
 * object that contains configuration data for this validator
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
interface ConfigInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $config         configuration data to add
     * @param           string          $configFile     path to the configuration file
     */
    public function __construct(array $config, string $configFile);

    /**
     * gets a configuration entry from the object
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $keys           keys to find the config entry
     */
    public function get(string ...$keys) : mixed;

    /**
     * returns the path to the folder containing the config file
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getConfigFolder() : string;
}
