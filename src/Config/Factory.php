<?php
/**
 * contains Config Factory class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Config;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;

/**
 * factory to create a config object
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class Factory
{
    /**
     * configuration data
     * @var     mixed[]
     */
    private array $config = [];

    /**
     * creates a configuration object from a json file
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           file to read the data from
     */
    public static function fromJson(string $file) : ConfigInterface
    {
        $fileContent = self::getDataFromFile($file);

        try {
            $data = json_decode(
                json:        $fileContent,
                associative: true,
                flags:       JSON_THROW_ON_ERROR
            );
        } catch (\Exception $e) {
            throw new \Exception(
                "unable to decode json data",
                $e->getCode(),
                $e
            );
        }

        $config = self::addFromArguments($data, $file);

        return $config;
    }


    /**
     * adds data from cli-arguments to config data and creates data object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $data           data to create the Config instance with
     * @param           string          $file           name of the configuration file
     */
    private static function addFromArguments(array $data, string $file) : ConfigInterface
    {
        $arguments = $_SERVER['argv'] ?? [];

        if (in_array("--from-stdin", $arguments, true)) {
            $data['parameters']['fromstdin'] = true;
        }

        return new Config($data, $file);
    }


    /**
     * fetches the content from a file
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           file to fetch data from
     */
    private static function getDataFromFile(string $file) : string
    {
        if (!file_exists($file)) {
            throw new \Exception("configuration file '".$file."' does not exist");
        }

        $fileContent = file_get_contents($file);

        if ($fileContent === false) {
            throw new \Exception("unable to read data from configuration file '".$file."'");
        }

        return $fileContent;
    }
}
