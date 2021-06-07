<?php declare(strict_types=1);

/**
 * contains Config Factory class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator\Config;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;
use DavidLienhard\Database\QueryValidator\Exceptions\Config as ConfigException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml;

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
    public static function fromJson(Filesystem $filesystem, string $file) : ConfigInterface
    {
        $fileContent = self::getDataFromFile($filesystem, $file);

        try {
            $data = json_decode(
                json:        $fileContent,
                associative: true,
                flags:       JSON_THROW_ON_ERROR
            );
        } catch (\Exception $e) {
            throw new ConfigException(
                "unable to decode json data",
                $e->getCode(),
                $e
            );
        }

        $config = self::addFromArguments($data);

        return $config;
    }

    public static function fromYaml(Filesystem $filesystem, string $file) : ConfigInterface
    {
        $fileContent = self::getDataFromFile($filesystem, $file);

        try {
            $data = Yaml::parse($fileContent) ?? [];
        } catch (YamlParseException $e) {
            throw new ConfigException(
                "unable to decode yaml data",
                $e->getCode(),
                $e
            );
        }

        $config = self::addFromArguments($data);

        return $config;
    }


    /**
     * adds data from cli-arguments to config data and creates data object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $data           data to create the Config instance with
     */
    private static function addFromArguments(array $data) : ConfigInterface
    {
        $arguments = $_SERVER['argv'] ?? [];

        if (in_array("--from-stdin", $arguments, true)) {
            $data['parameters']['fromstdin'] = true;
        }

        return new Config($data);
    }


    /**
     * fetches the content from a file
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           file to fetch data from
     */
    private static function getDataFromFile(Filesystem $filesystem, string $file) : string
    {
        if (!$filesystem->fileExists($file)) {
            throw new ConfigException("configuration file '".$file."' does not exist");
        }

        try {
            $fileContent = $filesystem->read($file);
        } catch (FilesystemException | UnableToReadFile $e) {
            throw new ConfigException("unable to read data from configuration file '".$file."'", $e->getCode(), $e);
        }

        return $fileContent;
    }
}
