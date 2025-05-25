<?php declare(strict_types=1);

/**
 * contains Config class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

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
     */
    public function __construct(private array $config)
    {
    }

    /**
     * gets a configuration entry from the object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $keys           keys to find the config entry
     */
    public function get(string ...$keys) : string|int|float|bool|null
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
}
