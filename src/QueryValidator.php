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
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

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
        $filesystem = self::getFilesystem();
        $output = new StandardOutput;

        try {
            $config = self::getConfig($filesystem);
        } catch (\Exception $e) {
            $output->error(
                "error fetching configuration-data".PHP_EOL.
                "    ".$e->getMessage().PHP_EOL
            );
            exit(1);
        }

        $paths = self::getPaths($config);
        $exclusions = self::getExclusions($config);
        $dumpData = self::getDumpData($config);

        ini_set("xdebug.max_nesting_level", "1000");

        $tester = new Tester($filesystem, $config, $output, $dumpData);

        $fromStdin = boolval($config->get("parameters", "fromstdin") ?? false);

        $scanner = !$fromStdin
            ? new FilesystemScanner($tester)
            : new StdinScanner($tester);

        $scanner->scan(
            $paths,
            dirname(__DIR__, 4),
            $exclusions
        );

        exit((int) $output->summary($tester));
    }

    /**
     * fetchs config
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    private static function getConfig(Filesystem $filesystem) : ConfigInterface
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
                $config = ConfigFactory::fromJson($filesystem, $configFile['filename']);
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
        $paths = $config->get("paths");

        if ($paths === null) {
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
        $exclusions = $config->get("exclusions") ?? [];

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
        $dumpFile = $config->get("dumpfile");

        if ($dumpFile === null) {
            return new DumpData;
        }

        if (!file_exists($dumpFile)) {
            throw new \Exception("given dump file '".$dumpFile."' does not exist");
        }

        $filesystem = self::getFilesystem();
        return FromMysqlDump::getDumpData($filesystem, $dumpFile);
    }

    private static function getFilesystem() : Filesystem
    {
        $adapter = new LocalFilesystemAdapter("/");
        return new Filesystem($adapter);
    }
}
