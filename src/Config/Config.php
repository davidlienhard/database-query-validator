<?php
/**
 * contains Config class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Config;

use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;

/**
 * object that contains configuration data for this validator
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class Config implements ConfigInterface
{
    /**
     * sets dependencies
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $config         configuration data to add
     * @param           string          $configFile     path to the configuration file
     */
    public function __construct(private array $config, private string $configFile)
    {
    }

    /**
     * gets a configuration entry from the object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $keys           keys to find the config entry
     */
    public function get(string ...$keys) : mixed
    {
        $data = $this->config;
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return null;
            }

            $data = $data[$key];
        }

        return $data;
    }

    /**
     * returns the path to the folder containing the config file
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getConfigFolder() : string
    {
        return dirname($this->configFile);
    }
}
