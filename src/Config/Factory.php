<?php
/**
 * contains Config Factory class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Config;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;

/**
 * factory to create a config object
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
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
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           file to read the data from
     */
    public static function fromJson(string $file) : ConfigInterface
    {
        if (!file_exists($file)) {
            throw new \Exception("configuration file '".$file."' does not exist");
        }

        $fileContent = file_get_contents($file);

        if ($fileContent === false) {
            throw new \Exception("unable to read data from configuration file '".$file."'");
        }

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

        return new Config($data, $file);
    }
}
