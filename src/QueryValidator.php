<?php
/**
 * main entrypoint to validate queries
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator;

use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;
use DavidLienhard\Database\QueryValidator\Config\Factory as ConfigFactory;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\DumpData\FromMysqlDump;
use DavidLienhard\Database\QueryValidator\Output\Standard as StandardOutput;
use DavidLienhard\Database\QueryValidator\Scanner\FilesystemScanner;
use DavidLienhard\Database\QueryValidator\Scanner\StdinScanner;
use DavidLienhard\Database\QueryValidator\Tester\Tester;

/**
 * main entrypoint to validate queries
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class QueryValidator
{
    /**
     * main method to call
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public static function main() : void
    {
        $output = new StandardOutput;

        try {
            $config = self::getConfig();
        } catch (\Exception $e) {
            $output->error(
                "error fetching configuration-data".PHP_EOL.
                "    ".$e->getMessage().PHP_EOL
            );
            exit;
        }

        $paths = self::getPaths($config);
        $exclusions = self::getExclusions($config);
        $dumpData = self::getDumpData($config);

        ini_set("xdebug.max_nesting_level", "1000");

        $tester = new Tester($config, $output, $dumpData);

        $fromStdin = boolval($config->get("parameters", "fromstdin") ?? false);

        $scanner = !$fromStdin
            ? new FilesystemScanner($tester)
            : new StdinScanner($tester);

        $scanner->scan(
            $paths,
            $config->getConfigFolder(),
            $exclusions
        );

        $output->summary($tester);
    }

    /**
     * fetchs config
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    private static function getConfig() : ConfigInterface
    {
        $configCandidates = [
            [
                "type"     => "json",
                "filename" => dirname(__DIR__, 4).DIRECTORY_SEPARATOR."query-validator.json"
            ]
        ];
        $configFile = null;
        foreach ($configCandidates as $candidate) {
            if (file_exists($candidate['filename'])) {
                $configFile = $candidate;
                break;
            }
        }

        if ($configFile === null) {
            throw new \Exception("no configuration file found");
        }

        switch ($configFile['type']) {
            case "json":
                $config = ConfigFactory::fromJson($configFile['filename']);
                break;
            default:
                throw new \Exception("unsupported configuration type '".$configFile['type']."'");
        }

        return $config;
    }

    /**
     * fetches paths to scan from configuration-data
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           ConfigInterface $config         configuration object to use
     */
    private static function getPaths(ConfigInterface $config) : array
    {
        try {
            $paths = $config->get("paths");
        } catch (\Exception $e) {
            $baseDirecory = dirname(__DIR__, 4);
            $paths = [ $baseDirecory ];
        }

        if (!is_array($paths)) {
            $paths = [ $paths ];
        }

        return $paths;
    }

    /**
     * fetches exclusions from configuration-data
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           ConfigInterface $config         configuration object to use
     */
    private static function getExclusions(ConfigInterface $config) : array
    {
        try {
            $exclusions = $config->get("exclusions");
        } catch (\Exception $e) {
            $exclusions = [];
        }

        if (!is_array($exclusions)) {
            $exclusions = [ $exclusions ];
        }

        return $exclusions;
    }

    /**
     * fetches dump data from database dump
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           ConfigInterface $config         configuration object to use
     */
    private static function getDumpData(ConfigInterface $config) : DumpData
    {
        try {
            $dumpFile = $config->get("dumpfile");
        } catch (\Exception $e) {
            return new DumpData;
        }

        if (!file_exists($dumpFile)) {
            throw new \Exception("given dump file '".$dumpFile."' does not exist");
        }

        return FromMysqlDump::getDumpData($dumpFile);
    }
}
